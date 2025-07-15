import $ from "jquery";

$(function() {
  "use strict";

  let csrfToken = $('meta[name="csrf-token"]').attr("content");

  // Handle Next button click to navigate between parts
  $(".next-part").on("click", function(e) {
    e.preventDefault();

    var nextPart = $(this).data("next-part");
    var currentPart = $(this).data("current-part");

    $.ajax({
      url: updateCurrentPartUrl,
      type: "POST",
      headers: {
        "X-CSRF-TOKEN": csrfToken
      },
      data: {
        user_id: window.userId,
        exam_id: window.examId,
        current_part: nextPart
      },
      success: function() {
        $("#part_" + currentPart).hide();
        $("#part_" + nextPart).show();
      },
      error: function(xhr) {
        console.error("Failed to update part. Please try again.");
      }
    });
  });
});
