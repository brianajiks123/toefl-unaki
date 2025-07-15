import $ from "jquery";
import Swal from "sweetalert2";

$(function() {
  "use strict";

  // Settings Tasks //
  // Handle Edit Profile Button
  $(".editProfileBtn").on("click", function(e) {
    e.preventDefault();

    $("#updateProfile input").each(function() {
      $(this).prop("disabled", false);
    });

    $("#updateProfile textarea").each(function() {
      $(this).prop("disabled", false);
    });

    $(".editProfileBtn").addClass("d-none");

    $(".updateProfileBtn").removeClass("d-none");
    $(".cancelUpdateProfileBtn").removeClass("d-none");
  });

  // Handle Cancel Update Profile Button
  $(".cancelUpdateProfileBtn").on("click", function(e) {
    e.preventDefault();

    $("#updateProfile input").each(function() {
      $(this).prop("disabled", true);
    });

    $("#updateProfile textarea").each(function() {
      $(this).prop("disabled", true);
    });

    $(".editProfileBtn").removeClass("d-none");

    $(".updateProfileBtn").addClass("d-none");
    $(".cancelUpdateProfileBtn").addClass("d-none");
  });

  // Update Profile
  $("#updateProfile").on("submit", function(e) {
    e.preventDefault();

    $(".updateProfileBtn").find("i").removeClass("ti ti-check");
    $("#spinner_updateProfile").removeClass("d-none");
    $(".cancelUpdateProfileBtn").addClass("d-none");

    $.ajax({
      url: updateProfileUrl,
      type: "POST",
      data: $(this).serialize(),
      success: function(data) {
        if (data.success == true) {
          $("#spinner_updateProfile").addClass("d-none");
          $(".updateProfileBtn").find("i").addClass("ti ti-check");
          $(".updateProfileBtn").addClass("d-none");
          $(".cancelUpdateProfileBtn").addClass("d-none");
          $(".editProfileBtn").removeClass("d-none");
          $("#updateProfile input").each(function() {
            $(this).prop("disabled", true);
          });
          $("#updateProfile textarea").each(function() {
            $(this).prop("disabled", true);
          });

          Swal.fire({
            title: "Success",
            text: data.msg,
            icon: "success",
            confirmButtonText: "OK"
          });
        } else {
          $("#spinner_updateProfile").addClass("d-none");
          $(".updateProfileBtn").find("i").addClass("ti ti-check");
          $(".updateProfileBtn").removeClass("d-none");
          $(".cancelUpdateProfileBtn").removeClass("d-none");
          $(".editProfileBtn").addClass("d-none");

          Swal.fire({
            title: "Failed!",
            text: data.msg,
            icon: "error",
            confirmButtonText: "OK"
          });
        }
      },
      error: function(xhr, status, error) {
        $("#spinner_updateProfile").addClass("d-none");
        $(".updateProfileBtn").find("i").addClass("ti ti-check");
        $(".updateProfileBtn").removeClass("d-none");
        $(".cancelUpdateProfileBtn").removeClass("d-none");
        $(".editProfileBtn").addClass("d-none");

        let errorMsg = "An error occurred. Please try again later.";

        if (xhr.responseJSON && xhr.responseJSON.msg) {
          errorMsg = xhr.responseJSON.msg;
        }

        Swal.fire({
          title: "Error",
          text: errorMsg,
          icon: "error",
          confirmButtonText: "OK"
        });
      }
    });
  });

  // Update Password
  $("#updatePassword").on("submit", function(e) {
    e.preventDefault();

    $("#spinner_updatePassword").removeClass("d-none");

    $.ajax({
      url: updatePasswordUrl,
      type: "POST",
      data: $(this).serialize(),
      success: function(data) {
        if (data.success == true) {
          $("#spinner_updatePassword").addClass("d-none");

          Swal.fire({
            title: "Success",
            text: data.msg,
            icon: "success",
            confirmButtonText: "OK"
          })
            .then(result => {
              if (result.isConfirmed) {
                location.reload();
              }
            })
            .catch(err => {
              Swal.fire({
                title: "Failed!",
                text: err,
                icon: "error",
                confirmButtonText: "OK"
              });
            });
        } else {
          $("#spinner_updatePassword").addClass("d-none");

          let errorMsg = data.errors
            ? Object.values(data.errors).join(" ")
            : data.msg;

          Swal.fire({
            title: "Failed!",
            text: errorMsg,
            icon: "error",
            confirmButtonText: "OK"
          });
        }
      },
      error: function(xhr, status, error) {
        $("#spinner_updatePassword").addClass("d-none");

        let errorMsg = "An error occurred. Please try again later.";

        if (xhr.responseJSON && xhr.responseJSON.msg) {
          errorMsg = xhr.responseJSON.msg;
        }

        Swal.fire({
          title: "Error",
          text: errorMsg,
          icon: "error",
          confirmButtonText: "OK"
        });
      }
    });
  });
});
