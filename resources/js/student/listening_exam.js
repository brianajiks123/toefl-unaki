import $ from "jquery";

$(function() {
  "use strict";

  const csrfToken = $('meta[name="csrf-token"]').attr("content");

  function updateAudioStatus(userId, fileListeningId, playedStatus) {
    $.ajax({
      url: updateStatusFileListeningUrl,
      type: "POST",
      headers: { "X-CSRF-TOKEN": csrfToken },
      data: {
        id_user: userId,
        id_file_listening: fileListeningId,
        played_status: playedStatus
      }
    });
  }

  function stopAudioPlayers() {
    $("audio").each(function() {
      const audioPlayer = this;
      const button = $(audioPlayer).siblings("button");

      if (!audioPlayer.paused) {
        audioPlayer.pause();
        audioPlayer.currentTime = 0;
        button.prop("disabled", true).text("Ended");
      }
    });
  }

  $(".next-part").on("click", function(e) {
    e.preventDefault();
    stopAudioPlayers();

    const nextPart = $(this).data("next-part");
    const currentPart = $(this).data("current-part");

    $.ajax({
      url: updateCurrentPartUrl,
      type: "POST",
      headers: { "X-CSRF-TOKEN": csrfToken },
      data: {
        user_id: window.user_id,
        exam_id: window.exam_id,
        current_part: nextPart
      },
      success: function() {
        $(`#part_${currentPart}`).hide();
        $(`#part_${nextPart}`).show();
      },
      error: function(xhr) {
        console.error("Failed to update part. Please try again.");
      }
    });
  });

  $(document).on("click", '[id^="play-button-"]', function(e) {
    e.preventDefault();

    const button = this;
    const audioId = button.id.replace("play-button-", "audio-player-");
    const audioPlayer = document.getElementById(audioId);

    const userId = $(button).data("user_id");
    const fileListeningId = $(button).data("file_listening_id");

    if (audioPlayer.paused) {
      audioPlayer.play();
      button.textContent = "Playing";

      audioPlayer.onended = function() {
        button.disabled = true;
        button.textContent = "Ended";
        updateAudioStatus(userId, fileListeningId, 1);
      };
    }
  });
});
