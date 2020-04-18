document.addEventListener("DOMContentLoaded", function() {
    // email
    let currEmailField = $("#currEmailField");
    let btnEditEmail = $("#btnEditEmail");
    let hiddenBtnEmail = $("#hiddenEmailBtn");
    let currEmail = $("#currEmail");
    // password
    let btnEditPswd = $("#btnEditPswd");
    let hiddenPswdBtn = $("#hiddenPswdBtn");
    let currPswdField = $("#currPswdField");
    let currPswd = $("#currPswd");

    hiddenBtnEmail.addClass('hide');
    hiddenPswdBtn.addClass('hide');

    currEmailField.prop('disabled', true);
    currPswdField.prop('disabled', true);


    $(btnEditEmail).click(function() {
        if (currEmailField.prop('disabled')) {
            hiddenBtnEmail.removeClass("hide");
            btnEditEmail.remove();
            currEmailField.prop('disabled', false);
            currEmail.html('New Email');
        }
    });
    $(btnEditPswd).click(function() {
        if (currPswdField.prop('disabled')) {
            hiddenPswdBtn.removeClass("hide");
            btnEditPswd.remove();
            currPswdField.prop('disabled', false);
            currPswd.html('Old Password');
        }
    });
});