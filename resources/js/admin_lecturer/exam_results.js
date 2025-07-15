import $ from "jquery";
import "datatables.net-bs5";
import Swal from "sweetalert2";

$(function() {
  "use strict";

  // See Exam Result
  $(".viewExamResultBtn").on("click", function() {
    const studentId = $(this).data("student_id");
    const batchId = $(this).data("batch_id");

    Swal.fire({
      title: "Being processed",
      html: "Please wait a moment...",
      icon: "info",
      allowOutsideClick: false,
      showConfirmButton: false
    });

    $.ajax({
      url: showStudentsExamResultUrl
        .replace(":studentId", studentId)
        .replace(":batchId", batchId),
      type: "GET",
      success: function(response) {
        Swal.close();

        if (response.success) {
          // Clear any existing rows in the exam result table
          $("#exam_result_table tbody").empty();

          // Append the exam results to the table
          let examResults = response.exam_results;
          let examMsg = response.exam_msg;

          if (examResults != null) {
            let listening =
              examResults.Listening != undefined ? examResults.Listening : 0;
            let structure =
              examResults.Structure != undefined ? examResults.Structure : 0;
            let reading =
              examResults.Reading != undefined ? examResults.Reading : 0;
            let final_score =
              examResults.final_score != undefined
                ? examResults.final_score
                : 0;
            let resultRow = `
                            <tr>
                                <td>${listening}</td>
                                <td>${structure}</td>
                                <td>${reading}</td>
                                <td>${final_score}</td>
                            </tr>
                        `;
            $("#exam_result_table tbody").append(resultRow);

            if (examMsg != "") {
                $("#caption_table").text("");
                $("#caption_table").html(examMsg);
            } else {
                $("#caption_table").text("");
            }
          } else {
            let resultRow = `
                            <tr>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                            </tr>
                        `;
            $("#exam_result_table tbody").append(resultRow);
            $("#caption_table").text("");
          }
        } else {
          // Clear any existing rows in the exam result table
          $("#exam_result_table tbody").empty();

          let resultRow = `
                      <tr>
                          <td>0</td>
                          <td>0</td>
                          <td>0</td>
                          <td>0</td>
                      </tr>
                  `;
          $("#exam_result_table tbody").append(resultRow);
          $("#caption_table").text("");
          $("#caption_table").text("The user has not yet taken the test.");
        }
      },
      error: function(xhr, status, error) {
        Swal.close();

        // Clear any existing rows in the exam result table
        $("#exam_result_table tbody").empty();

        let resultRow = `
                    <tr>
                        <td>0</td>
                        <td>0</td>
                        <td>0</td>
                        <td>0</td>
                    </tr>
                `;
        $("#exam_result_table tbody").append(resultRow);
        $("#caption_table").text("");
        $("#caption_table").text("Server internal error");
      }
    });
  });

  // Delete Exam Result
  $(document).on("click", ".deleteExamResultBtn", function() {
    $("#delete_batchid").val($(this).data("batch_id"));
    $("#delete_userid").val($(this).data("student_id"));
  });

  $("#deleteExamResult").on("submit", function(e) {
    e.preventDefault();

    $("#spinner_delete").removeClass("d-none");

    $.ajax({
      url: deleteExamResultUrl,
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
