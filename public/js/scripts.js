$("#back_button").click(function() {
    window.location = "/";
});

function deleteContact(id)
{
    $.ajax({
        type: "DELETE",
        url: "/manage?id=" + id,
        success: function(msg) {
            window.location = "/";
        }
    });
}
