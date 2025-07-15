import $ from "jquery";
import "datatables.net-bs5";
import Swal from "sweetalert2";

$(function() {
  "use strict";

  // Initialize DataTable if data exist
  $('table[id^="listening_quests_"]').each(function() {
    $(this).DataTable({
      pageLength: 3
    });
  });

  // Play Audio File Listening
  $(document).on("click", '[id^="play-button-"]', function() {
    var button = this;
    var buttonId = button.id;
    var audioId = buttonId.replace("play-button-", "audio-player-");
    var audioPlayer = document.getElementById(audioId);

    if (audioPlayer.paused) {
      audioPlayer.play();
      button.textContent = "Pause";

      audioPlayer.onended = function() {
        button.disabled = true;
        button.textContent = "Play";
      };
    } else {
      audioPlayer.pause();
      button.textContent = "Play";
    }
  });

  // Listening Question Tasks //
  // Add Listening Question
  $("#addListenQuest").on("submit", function(e) {
    e.preventDefault();

    $("#spinner_add").removeClass("d-none");

    var formData = new FormData(this);

    $.ajax({
      url: addListeningQuestionUrl,
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

  // Change Audio File Listening
  $(document).on("click", ".editAudioFileBtn", function() {
    $("#batch_name").val($(this).data("batch_name"));
    $("#audio_file_id").val($(this).data("file_listening_id"));
  });

  $("#editAudioFile").on("submit", function(e) {
    e.preventDefault();

    var formData = new FormData(this);

    $(".updateAudioFileListenBtn").prop("disabled", true);

    Swal.fire({
      title: "Being processed",
      html: "Please wait a moment...",
      icon: "info",
      allowOutsideClick: false,
      showConfirmButton: false
    });

    $.ajax({
      url: updateFileListeningUrl,
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
          $(".updateAudioFileListenBtn").prop("disabled", false);

          if (result.isConfirmed && data.success) {
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
        }).then(result => {
          $(".updateAudioFileListenBtn").prop("disabled", false);
        });
      }
    });
  });

  // Add Listening Option Answer (Manual)
  $(".addListenOptAnsBtn").on("click", function() {
    $("#audio_file_id_manual").val($(this).data("audio_file_id"));
  });

  $("#addListenOptAns").on("submit", function(e) {
    e.preventDefault();

    $("#spinner_add").removeClass("d-none");

    $.ajax({
      url: listenOptionAnswerManualUrl,
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

  // Add Listening Option Answer (File)
  $(".addListenOptAnsFileBtn").on("click", function() {
    $("#audio_file_id_file").val($(this).data("audio_file_id"));
    $("#audio_file_id_batch").val($(this).data("batch_id"));
    $("#audio_file_id_part").val($(this).data("audio_part"));
  });

  $("#addListenOptAnsFile").on("submit", function(e) {
    e.preventDefault();

    $("#spinner_add").removeClass("d-none");

    var formData = new FormData(this);

    $.ajax({
      url: listenOptionAnswerFileUrl,
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

  // Edit Listening Option Answer
  $(document).on("click", ".editListenOptAnsBtn", function() {
    $("#edit_option_1Id").val($(this).data("id"));
    $("#edit_option_ans_1").val($(this).data("option_1"));
    $("#edit_option_ans_2").val($(this).data("option_2"));
    $("#edit_option_ans_3").val($(this).data("option_3"));
    $("#edit_option_ans_4").val($(this).data("option_4"));
    $("#edit_ans_correct").val($(this).data("ans_correct"));
  });

  $("#editListenOptAns").on("submit", function(e) {
    e.preventDefault();

    $(".updateListenOptAnsBtn").prop("disabled", true);

    $("#spinner_edit").removeClass("d-none");

    $.ajax({
      url: editListenOptionAnswerUrl,
      type: "POST",
      data: $(this).serialize(),
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
        $(".updateListenOptAnsBtn").prop("disabled", false);

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

  // Delete Listening Option Answer
  $(document).on("click", ".deleteListenOptAnsBtn", function() {
    $("#delete_listenOptAnsId").val($(this).data("id"));
  });

  $("#deleteListenOptAns").on("submit", function(e) {
    e.preventDefault();

    $("#spinner_delete").removeClass("d-none");

    $.ajax({
      url: deleteListenOptionAnswerUrl,
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
