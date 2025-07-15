import $ from "jquery";
import "datatables.net-bs5";
import Swal from "sweetalert2";

$(function() {
  "use strict";

  // Initialize DataTable if data exist
  if (users.length > 0) {
    $("#users_table").DataTable({
      pageLength: 3
    });
  }

  const csrfToken = $('meta[name="csrf-token"]').attr("content");

  // User Tasks //
  // Add User
  $("#addUser").on("submit", function(e) {
    e.preventDefault();

    $("#spinner_add").removeClass("d-none");

    $.ajax({
      url: addUserUrl,
      type: "POST",
      data: $(this).serialize(),
      success: function(data) {
        $("#spinner_add").addClass("d-none");
        Swal.fire({
          title: data.success ? "Success" : "Failed!",
          text: data.msg,
          icon: data.success ? "success" : "error",
          confirmButtonText: "OK"
        }).then(result => {
          if (result.isConfirmed && data.success) {
            location.reload();
          }
        });
      },
      error: function(xhr, status, error) {
        $("#spinner_add").addClass("d-none");

        let errorMsg = "An unexpected error occurred. Please try again later.";

        if (xhr.responseJSON) {
          if (xhr.responseJSON.errors) {
            errorMsg = Object.values(xhr.responseJSON.errors).flat().join(", ");
          } else if (xhr.responseJSON.msg) {
            errorMsg = xhr.responseJSON.msg;
          }
        } else {
          errorMsg = xhr.statusText || error;
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

  // Reverify User
  $(document).on("click", ".reverifyBtn", function() {
    Swal.fire({
      title: "Being processed",
      html: "Please wait a moment...",
      icon: "info",
      allowOutsideClick: false,
      showConfirmButton: false
    });

    $.ajax({
      url: resendVerifyUrl,
      type: "POST",
      data: {
        id: $(this).data("id"),
        _token: csrfToken
      },
      success: function(data) {
        Swal.close();

        Swal.fire({
          title: data.success ? "Success" : "Failed!",
          text: data.msg,
          icon: data.success ? "success" : "error",
          confirmButtonText: "OK"
        }).then(result => {
          if (data.success && result.isConfirmed) {
            location.reload();
          }
        });
      },
      error: function(xhr, status, error) {
        Swal.close();

        let errorMsg = "An unexpected error occurred. Please try again later.";

        if (xhr.responseJSON) {
          if (xhr.responseJSON.errors) {
            errorMsg = Object.values(xhr.responseJSON.errors).flat().join(", ");
          } else if (xhr.responseJSON.msg) {
            errorMsg = xhr.responseJSON.msg;
          }
        } else {
          errorMsg = xhr.statusText || error;
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

  // Edit User
  $(document).on("click", ".editUserBtn", function() {
    $("#edit_userid").val($(this).data("id"));
    $("#edit_username").val($(this).data("name"));
    $("#edit_useremail").val($(this).data("email"));
  });

  $("#editUser").on("submit", function(e) {
    e.preventDefault();

    $(".updateUserBtn").prop("disabled", true);

    var formData = $(this).serialize();

    $("#spinner_edit").removeClass("d-none");

    $.ajax({
      url: editUserUrl,
      type: "POST",
      data: formData,
      success: function(data) {
        $("#spinner_edit").addClass("d-none");

        Swal.fire({
          title: data.success ? "Success" : "Failed!",
          text: data.msg,
          icon: data.success ? "success" : "error",
          confirmButtonText: "OK"
        }).then(result => {
          if (data.success && result.isConfirmed) {
            location.reload();
          }
        });
      },
      error: function(xhr, status, error) {
        $("#spinner_edit").addClass("d-none");

        let errorMsg = "An unexpected error occurred. Please try again later.";

        if (xhr.responseJSON) {
          if (xhr.responseJSON.errors) {
            errorMsg = Object.values(xhr.responseJSON.errors).flat().join(", ");
          } else if (xhr.responseJSON.msg) {
            errorMsg = xhr.responseJSON.msg;
          }
        } else {
          errorMsg = xhr.statusText || error;
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

  // Delete User
  $(document).on("click", ".deleteUserBtn", function() {
    $("#delete_userid").val($(this).data("id"));
  });

  $("#deleteUser").on("submit", function(e) {
    e.preventDefault();

    $("#spinner_delete").removeClass("d-none");

    $.ajax({
      url: deleteUserUrl,
      type: "POST",
      data: $(this).serialize(),
      success: function(data) {
        $("#spinner_delete").addClass("d-none");

        Swal.fire({
          title: data.success ? "Success" : "Failed!",
          text: data.msg,
          icon: data.success ? "success" : "error",
          confirmButtonText: "OK"
        }).then(result => {
          if (data.success && result.isConfirmed) {
            location.reload();
          }
        });
      },
      error: function(xhr, status, error) {
        $("#spinner_delete").addClass("d-none");

        let errorMsg = "An unexpected error occurred. Please try again later.";

        if (xhr.responseJSON) {
          if (xhr.responseJSON.errors) {
            errorMsg = Object.values(xhr.responseJSON.errors).flat().join(", ");
          } else if (xhr.responseJSON.msg) {
            errorMsg = xhr.responseJSON.msg;
          }
        } else {
          errorMsg = xhr.statusText || error;
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
