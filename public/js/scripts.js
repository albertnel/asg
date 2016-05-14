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

