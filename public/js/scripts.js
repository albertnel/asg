function deleteContact(id)
{
    if (confirm("Are you sure you want to delete this contact?")) {
        $.ajax({
            type: "DELETE",
            url: "/manage?id=" + id,
            success: function(data) {
                var json = JSON.parse(data);
                window.location = "/?deleted=" + json.deleted;
            }
        });
    }
}

$(document).ready(function() {
    $("#back_button").click(function() {
        window.location = "/";
    });

    $('#filter').keyup(function () {
        var rex = new RegExp($(this).val(), 'i');
        $('.searchable tr').hide();
        $('.searchable tr').filter(function () {
            return rex.test($(this).text());
        }).show();
    })
});

