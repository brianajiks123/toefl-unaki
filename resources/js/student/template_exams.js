import "bootstrap";
import $ from "jquery";
import Swal from "sweetalert2";

let timerInterval;
let saveInterval;

$(function() {
  "use strict";

  // Disable Right Click, F12/Ctrl+Shift+I (Developer Tools), Ctrl+Shift+J (Console Browser), CTRL + U (View Page Source), Copy
  $(document).on("contextmenu", function(e) {
    e.preventDefault();
  });
  $(document).on("keydown", function(e) {
    // F12
    if (e.keyCode === 123) {
      e.preventDefault();
    }

    // Ctrl+U and Ctrl+Shift+I
    if (e.ctrlKey && (e.keyCode === 85 || e.keyCode === 73)) {
      e.preventDefault();
    }

    // Ctrl+Shift+I and Ctrl+Shift+J
    if (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74)) {
      e.preventDefault();
    }
  });
  function disableCopy(e) {
    e.preventDefault();
  }
  document.addEventListener("copy", disableCopy);

  // Update current part & exam session while first load page
  function updateData(url, data, onSuccess, onError) {
    $.ajax({
      url: url,
      type: "POST",
      headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
      },
      data: data,
      success: onSuccess,
      error: onError
    });
  }

  updateData(
    updateCurrentPartUrl,
    {
      user_id: window.user_id,
      exam_id: window.exam_id,
      current_part: 1
    },
    function() {
      $(`#part_1`).show();
    },
    function(xhr) {
      console.error("Failed to update part. Please try again.");
    }
  );

  updateData(
    updateExamSessionUrl,
    {
      user_id: window.user_id,
      exam_id: window.exam_id
    },
    function() {
      $(`#part_1`).show();
    },
    function(xhr) {
      console.error("Failed to update exam session. Please try again.");
    }
  );

  const csrfToken = $('meta[name="csrf-token"]').attr("content");
  const remainingTimeInput = document.getElementById("remaining-time");
  const timerDisplay = document.getElementById("exam-timer");
  const examId = window.examId;

  function saveUpdateRemainingTime(remainingTime) {
    $.ajax({
      url: saveUpdateRemainingTimeUrl,
      method: "POST",
      headers: { "X-CSRF-TOKEN": csrfToken },
      data: { exam_id: examId, remaining_time: remainingTime }
    });
  }

  function formatTime(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor(seconds % 3600 / 60);
    const secs = seconds % 60;

    return [hours, minutes, secs]
      .map(val => (val < 10 ? "0" + val : val))
      .join(":");
  }

  function startTimer(duration, display) {
    let timer = duration;

    saveInterval = setInterval(function() {
      saveUpdateRemainingTime(formatTime(timer));
    }, 1000);

    timerInterval = setInterval(function() {
      display.textContent = formatTime(timer);

      if (--timer < 0) {
        clearInterval(timerInterval);
        clearInterval(saveInterval);
        display.textContent = "00:00:00";
        saveUpdateRemainingTime("00:00:00");
        $("#examFinished").trigger("submit");
      }
    }, 1000);
  }

  if (remainingTimeInput) {
    const timeParts = remainingTimeInput.value.split(":");
    const totalSeconds =
      parseInt(timeParts[0], 10) * 3600 +
      parseInt(timeParts[1], 10) * 60 +
      parseInt(timeParts[2], 10);
    startTimer(totalSeconds, timerDisplay);
  }

  $(document).on("submit", "#examFinished", function(e) {
    e.preventDefault();

    clearInterval(timerInterval);
    clearInterval(saveInterval);

    remainingTimeInput.value = "00:00:00";
    saveUpdateRemainingTime(remainingTimeInput.value);

    const formData = {};

    $(".part-section").each(function() {
      $(this).find("input:radio:checked").each(function() {
        const questionId = $(this).attr("name");
        const answer = $(this).val();

        if (!formData.hasOwnProperty(questionId)) {
          formData[questionId] = answer;
        }
      });
    });

    $("#examFinished").serializeArray().forEach(function(field) {
      if (!formData.hasOwnProperty(field.name)) {
        formData[field.name] = field.value;
      }
    });

    const serializedData = $.param(formData);

    $.ajax({
      url: submitExamUrl,
      method: "POST",
      data: serializedData,
      success: function(response) {
        if (response.success) {
          Swal.fire({
            title: "Exam Completed!",
            text: `You successfully answered: ${response.correct_answers} out of ${response.totalQuestions} questions correctly with total unanswered: ${response.unanswered_questions}.`,
            icon: "success"
          }).then(() => {
            window.location.href = "/";
          });
        } else {
          Swal.fire({
            title: "Failed!",
            text: response.message,
            icon: "error"
          });
        }
      },
      error: function(xhr) {
        Swal.fire({
          title: "Error!",
          text: xhr.responseJSON.msg,
          icon: "error"
        });
      }
    });
  });
});
