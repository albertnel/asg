function deleteContact(id)
{
    $.ajax({
        type: "DELETE",
        url: "/manage?id=" + id,
        success: function(data) {
            var json = JSON.parse(data);
            window.location = "/?deleted=" + json.success;
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

