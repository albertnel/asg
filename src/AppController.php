<?php

/**
*   AppController class.
*
*   Handles all the routing and page redirects, as well as
*   generating and displaying the content.
*
*   @author Albert Nel
*/

class AppController
{
    /**
    *   Handles the index page.
    *
    *   This function queries the DB for all contacts in alphabetical order.
    *
    *   It also handles an alert message to indicate whethere a contact was
    *   saved successfully or not.
    *
    *   Finally it renders the Twig template.
    */
    public function displayIndexPage()
    {
        $parse = [];

        // Query all contacts
        $parse['contacts'] = DB::query("SELECT * FROM contacts ORDER BY first_name, surname DESC");

        // Check profile photos
        $len = count($parse['contacts']);
        for ($i = 0; $i < $len; $i++) {
            $parse['contacts'][$i] = $this->checkProfilePhoto($parse['contacts'][$i]);
        }

        // Parse query string
        $query_array = parse_query_string($_SERVER['QUERY_STRING']);

        // Configure alert if necessarry
        $parse['alert'] = $this->getAlert($query_array);

        // Render template
        $twig = loadTwig();
        echo $twig->render('contacts_list.html', $parse);
    }

    /**
    *   Get alert message.
    *
    *   Gets the alert message and styling for saving and deleting actions.
    *
    *   @param array $query_array Query array from URI.
    *   @return array $alert Array with all alert details.
    */
    private function getAlert($query_array)
    {
        $alert = [];

        if (array_key_exists('success', $query_array)) {
            if ($query_array['success'] == 1) {
                $alert['class'] = 'alert-success';
                $alert['headline'] = 'Success!';
                $alert['message'] = 'Contact saved successfully.';
            } else if ($query_array['success'] == 0) {
                $alert['class'] = 'alert-danger';
                $alert['headline'] = 'Error!';
                $alert['message'] = 'Contact could not be saved.';
            } else {
                $alert['class'] = 'hidden';
            }
        } else if (array_key_exists('deleted', $query_array)) {
            if ($query_array['deleted'] == 1) {
                $alert['class'] = 'alert-success';
                $alert['headline'] = 'Success!';
                $alert['message'] = 'Contact deleted.';
            } else if ($query_array['deleted'] == 0) {
                $alert['class'] = 'alert-danger';
                $alert['headline'] = 'Error!';
                $alert['message'] = 'Contact could not be deleted.';
            } else {
                $alert['class'] = 'hidden';
            }
        } else {
            $alert['class'] = 'hidden';
        }

        return $alert;
    }

    /**
    *   Check profile photo.
    *
    *   Check if profile photo exists for contact, otherwise display
    *   generic photo placeholder.
    *
    *   @param array $contact The contact array from the DB.
    *   @return array $contact Updated contact array.
    */
    private function checkProfilePhoto($contact)
    {
        if (empty($contact['profile_filename'])) {
            $contact['profile_filename'] = 'generic.png';
            $contact['thumb_filename'] = 'generic_thumb.png';
        }

        return $contact;
    }

    /**
    *   Contact page handler.
    *
    *   Function responsible for the display of a single contact.
    *   Also responsible for processing $_POST data when creating
    *   or editing contact.
    *
    *   Renders contact page.
    */
    public function handleContactPage()
    {
        // If $_POST, process first.
        if (!empty($_POST)) {
            // Start DB transaction.
            DB::startTransaction();
            try {
                // Do insert or update.
                DB::insertUpdate('contacts', array(
                    'id' => $_POST['id'],
                    'first_name' => $_POST['first_name'],
                    'surname' => $_POST['surname'],
                    'cellphone' => $_POST['cellphone'],
                    'email' => $_POST['email'],
                    'address' => $_POST['address']
                ));

                // Handle uploaded profile pic.
                if (!empty($_FILES['tmp_name'])) {
                    $this->uploadProfilePic($_FILES['profile_pic'], $_POST['id']);
                }

                DB::commit();
                return 1;

            // Catch exception if something went wrong.
            } catch (Exception $e) {
                // Roll back transaction.
                DB::rollback();
                return 0;
            }
        }

        // Parse query string
        $query_array = [];
        if ($_SERVER['QUERY_STRING']) {
            $query_array = parse_query_string($_SERVER['QUERY_STRING']);
        }

        // Render page
        $parse = [];

        if (!empty($query_array['id'])) {
            $parse['contact'] = DB::queryFirstRow("SELECT * FROM contacts WHERE id = %d", $query_array['id']);
            $parse['submit_button_text'] = 'Update';
        } else {
            $parse['contact'] = [];
            $parse['submit_button_text'] = 'Add';
        }

        $twig = loadTwig();
        echo $twig->render('contact_manage.html', $parse);
    }

    /**
    *   Uploads profile photo.
    *
    *   Uploads profile photo after doing validation checks.
    *   Also creates a thumbnail from uploaded photo.
    *
    *   @param array $file Array of file upload details from $_FILES.
    *   @param int $id Contact id.
    *   @return bool
    */
    private function uploadProfilePic($file, $id)
    {
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $target_dir = __DIR__ . '/../public/images/profile_pics';
        $target_filename = $id . '.' . $file_extension;
        $target_file_path = $target_dir . '/' . $target_filename;
        $valid = 1;

        $check_image = getimagesize($file["tmp_name"]);
        if ($check_image === false) {
            $valid = 0;
        }

        $valid_extensions = ['jpg', 'gif', 'png'];
        $found_extension = false;
        foreach ($valid_extensions as $ext) {
            if ($file_extension == $ext) {
                $found_extension = true;
                break;
            }
        }
        if (!$found_extension) {
            $valid = 0;
        }

        if (!$valid) {
            return false;
        } else {
            if (move_uploaded_file($file['tmp_name'], $target_file_path)) {
                $old_files = DB::queryFirstRow("SELECT profile_filename, thumb_filename FROM contacts WHERE id = " . $id);

                // Remove old profile files
                $old_file_path = $target_dir . '/' . $old_files['profile_filename'];
                if ($old_files['profile_filename'] != $target_filename && file_exists($old_file_path)) {
                    unlink($old_file_path);
                }
                $old_thumb_path = $target_dir . '/' . $old_files['thumb_filename'];
                if (file_exists($old_thumb_path)) {
                    unlink($old_thumb_path);
                }

                // Create a thumbnail from the image.
                $manager = new ImageManager(array('driver' => 'gd'));
                $image = $manager->make($target_file_path)->resize(25, 25);
                $thumb_filename = $id . '_thumb.' . $file_extension;
                $thumb_file_path = $target_dir . '/' . $thumb_filename;
                $image->save($thumb_file_path);

                // Update contact profile path
                DB::update('contacts',
                    array(
                        'profile_filename' => $target_filename,
                        'thumb_filename' => $thumb_filename
                    ),
                    'id = ' . $id
                );

                return true;
            } else {
                return false;
            }
        }
    }

    /**
    *   Handles the deleting of a contact.
    *
    *   @return int 1 for success, 0 for failure.
    */
    function deleteContact()
    {
        // Parse query string
        $query_array = parse_query_string($_SERVER['QUERY_STRING']);

        // Delete contact
        try {
            DB::delete('contacts', "id=%d", $query_array['id']);
            return 1;
        } catch (MeekroDBException $e) {
            return 0;
        }
    }
}

?>
