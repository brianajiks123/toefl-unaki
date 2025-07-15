import $ from "jquery";
import "datatables.net-bs5";
import Swal from "sweetalert2";

$(function() {
  "use strict";

  // Initialize DataTable if data exist
  $('table[id^="reading_quests_"]').each(function() {
    $(this).DataTable({
      pageLength: 1
    });
  });

  // Reading Comprehension Question Tasks //
  // Add Reading Questions
  $("#addReadingQuests").on("submit", function(e) {
    e.preventDefault();

    $("#spinner_add").removeClass("d-none");

    var formData = new FormData(this);

    $.ajax({
      url: addReadingQuestionUrl,
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
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

  // Change Image File
  $(document).on("click", ".editImageFileBtn", function() {
    $("#batch_name").val($(this).data("batch_name"));
    $("#image_file_id").val($(this).data("reading_id"));
    $("#image_file_part").val($(this).data("reading_part"));
  });

  $("#editImageFile").on("submit", function(e) {
    e.preventDefault();

    var formData = new FormData(this);

    Swal.fire({
      title: "Being processed",
      html: "Please wait a moment...",
      icon: "info",
      allowOutsideClick: false,
      showConfirmButton: false
    });

    $.ajax({
      url: updateFileReadingUrl,
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      success: function(data) {
        Swal.close();

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
        Swal.close();
        $(".updateImageFileReadingBtn").prop("disabled", false);

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

  // Add Reading Question (Manual)
  $(".addReadingQuestBtn").on("click", function() {
    $("#image_file_id_manual").val($(this).data("image_file_id"));
  });

  $("#addReadingQuest").on("submit", function(e) {
    e.preventDefault();

    $("#spinner_add").removeClass("d-none");

    $.ajax({
      url: readingQuestUrl,
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

  // Add Reading Question (File)
  $(".addReadingQuestFileBtn").on("click", function() {
    $("#image_file_id_file").val($(this).data("image_file_id"));
    $("#image_file_id_batch").val($(this).data("batch_id"));
    $("#image_file_id_part").val($(this).data("reading_part"));
  });

  $("#addReadingQuestFile").on("submit", function(e) {
    e.preventDefault();

    $("#spinner_add").removeClass("d-none");

    var formData = new FormData(this);

    $.ajax({
      url: readingQuestFileUrl,
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
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

  // Edit Reading Question
  $(document).on("click", ".editReadingQuestBtn", function() {
    $("#edit_reading_quest_id").val($(this).data("id"));
    $("#edit_reading_quest").val($(this).data("question"));
    $("#edit_option_ans_1").val($(this).data("option_1"));
    $("#edit_option_ans_2").val($(this).data("option_2"));
    $("#edit_option_ans_3").val($(this).data("option_3"));
    $("#edit_option_ans_4").val($(this).data("option_4"));
    $("#edit_ans_correct").val($(this).data("ans_correct"));
  });

  $("#editReadingQuestion").on("submit", function(e) {
    e.preventDefault();

    $(".updateReadingQuestBtn").prop("disabled", true);

    $("#spinner_edit").removeClass("d-none");

    $.ajax({
      url: editReadingQuestUrl,
      type: "POST",
      data: $(this).serialize(),
      success: function(data) {
        $("#spinner_edit").addClass("d-none");
        $(".updateReadingQuestBtn").prop("disabled", false);

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
        $("#spinner_edit").addClass("d-none");
        $(".updateReadingQuestBtn").prop("disabled", false);

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

  // Delete Reading Question
  $(document).on("click", ".deleteReadingQuestBtn", function() {
    $("#delete_readingQuestId").val($(this).data("id"));
  });

  $("#deleteReadingQuestion").on("submit", function(e) {
    e.preventDefault();

    $("#spinner_delete").removeClass("d-none");

    $.ajax({
      url: deleteReadingQuestUrl,
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
