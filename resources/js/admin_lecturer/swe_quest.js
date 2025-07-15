import $ from "jquery";
import "datatables.net-bs5";
import Swal from "sweetalert2";

$(function() {
  "use strict";

  // Initialize DataTable if data exist
  $('table[id^="swe_quests_"]').each(function() {
    $(this).DataTable({
      pageLength: 1
    });
  });

  $(document).on("keydown", "#swe_quest_manual", function(e) {
    if (e.ctrlKey && e.key === "u") {
      e.preventDefault();

      const textarea = e.target;
      const start = textarea.selectionStart;
      const end = textarea.selectionEnd;

      if (start !== end) {
        const selectedText = textarea.value.substring(start, end);
        const beforeText = textarea.value.substring(0, start);
        const afterText = textarea.value.substring(end);

        textarea.value = beforeText + "<u>" + selectedText + "</u>" + afterText;

        textarea.selectionStart = start;
        textarea.selectionEnd = start + 7 + selectedText.length;
      }
    }
  });

  // Structure & Written Expression Question Tasks //
  // Add Swe Question (Manual)
  $("#addSweQuestManual").on("submit", function(e) {
    e.preventDefault();

    $("#spinner_add").removeClass("d-none");

    $.ajax({
      url: addSweQuestionManualUrl,
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

  // Add Swe Question (File)
  $("#addSweQuestFile").on("submit", function(e) {
    e.preventDefault();

    $("#spinner_add").removeClass("d-none");

    var formData = new FormData(this);

    $.ajax({
      url: addSweQuestionFileUrl,
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

  // Edit Swe Question
  $(document).on("click", ".editSweQuestBtn", function() {
    $("#edit_swe_quest_id").val($(this).data("id"));
    $("#edit_swe_quest").val($(this).data("question"));
    $("#edit_option_ans_1").val($(this).data("option_1"));
    $("#edit_option_ans_2").val($(this).data("option_2"));
    $("#edit_option_ans_3").val($(this).data("option_3"));
    $("#edit_option_ans_4").val($(this).data("option_4"));
    $("#edit_ans_correct").val($(this).data("ans_correct"));
  });

  $("#editSweQuestion").on("submit", function(e) {
    e.preventDefault();

    $(".updateSweQuestBtn").prop("disabled", true);

    $("#spinner_edit").removeClass("d-none");

    $.ajax({
      url: editSweQuestionUrl,
      type: "POST",
      data: $(this).serialize(),
      success: function(data) {
        $("#spinner_edit").addClass("d-none");
        $(".updateSweQuestBtn").prop("disabled", false);

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
        $(".updateSweQuestBtn").prop("disabled", false);

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

  // Delete Swe Question
  $(document).on("click", ".deleteSweQuestBtn", function() {
    $("#delete_sweQuestId").val($(this).data("id"));
  });

  $("#deleteSweQuestion").on("submit", function(e) {
    e.preventDefault();

    $("#spinner_delete").removeClass("d-none");

    $.ajax({
      url: deleteSweQuestionUrl,
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
