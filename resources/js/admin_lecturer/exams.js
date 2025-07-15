import $ from "jquery";
import "datatables.net-bs5";
import Swal from "sweetalert2";

$(function() {
  "use strict";

  // Exam Tasks //
  // Add Exam
  $("#addExam").on("submit", function(e) {
    e.preventDefault();

    $("#spinner_add").removeClass("d-none");

    $.ajax({
      url: addExamUrl,
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
      error: function(xhr) {
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

  // Edit Exam
  $(document).on("click", ".editExamBtn", function() {
    $("#exam_id").val($(this).data("id"));
    $("#edit_exam_name").text($(this).data("name"));
    $("#edit_exam_date").val($(this).data("date"));
    $("#edit_exam_time").val($(this).data("time"));
    $("#edit_exam_attempt").val($(this).data("attempt"));
  });

  $("#updateExamForm").on("submit", function(e) {
    e.preventDefault();

    $("#spinner_update").removeClass("d-none");

    $.ajax({
      url: updateExamUrl,
      type: "POST",
      data: $(this).serialize(),
      success: function(data) {
        $("#spinner_update").addClass("d-none");

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
      error: function(xhr) {
        $("#spinner_update").addClass("d-none");

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

  // Delete Exam
  $(document).on("click", ".deleteExamBtn", function() {
    $("#delete_examId").val($(this).data("id"));
  });

  $("#deleteExamForm").on("submit", function(e) {
    e.preventDefault();

    $("#spinner_delete").removeClass("d-none");

    $.ajax({
      url: deleteExamUrl,
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
          if (result.isConfirmed && data.success) {
            location.reload();
          }
        });
      },
      error: function(xhr) {
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
