import "bootstrap";
import $ from "jquery";
import Swal from "sweetalert2";

$(function() {
  $("#goLogin").on("submit", function(e) {
    e.preventDefault();

    $.ajax({
      url: userLogin,
      type: "POST",
      data: $(this).serialize(),
      success: function(data) {
        Swal.fire({
          title: data.success ? "&#9989" : "&#10060",
          text: data.msg,
          confirmButtonText: "OK"
        }).then(result => {
          if (result.isConfirmed && data.success) {
            window.location.href = data.route;
          }
        });
      },
      error: function(xhr, status, error) {
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
          title: "&#10060",
          text: errorMsg,
          icon: "error",
          confirmButtonText: "OK"
        });
      }
    });
  });
});
