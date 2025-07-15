import $ from "jquery";
import "datatables.net-bs5";
import Swal from "sweetalert2";

$(function() {
  "use strict";

  // Initialize DataTable if data exist
  if (batches.length > 0) {
    $("#batches_table").DataTable({
      pageLength: 3
    });
  }

  // Initialize User of Batch DataTables
  function initializeDataTable(batchId) {
    $("#users_batch_table\\[" + batchId + "\\]").DataTable({
      pageLength: 5
    });
  }

  // Add Batch
  $("#addBatch").on("submit", function(e) {
    e.preventDefault();

    $("#spinner_add").removeClass("d-none");

    $.ajax({
      url: addBatchUrl,
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

  // Delete Batch
  $(document).on("click", ".deleteBatchBtn", function() {
    $("#delete_batchid").val($(this).data("id"));
  });

  $("#deleteBatch").on("submit", function(e) {
    e.preventDefault();

    $("#spinner_delete").removeClass("d-none");

    $.ajax({
      url: deleteBatchUrl,
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

  // See Student of Batch
  $(".viewStudentsBatchBtn").on("click", function() {
    var batchId = $(this).data("id");
    var addStudentBatch = document.getElementById("add_student_batch");

    $.ajax({
      url: showStudentsBatchUrl.replace(":batchId", batchId),
      type: "GET",
      success: function(response) {
        var { users, users_ready } = response;
        var userlistField = $(".user_list_option").empty();
        var field = $(".user_list_option_table").empty();

        if (users_ready.length > 0) {
          addStudentBatch.classList.remove("d-none");

          var selectOption = $("<select>", {
            name: "user_list[]",
            id: "user_list_select",
            class: "form-select fs-6",
            multiple: true,
            required: true
          }).append(
            $("<option>", {
              value: "",
              text: "-- select student --",
              disabled: true,
              selected: true
            })
          );

          $.each(users_ready, function(_, user) {
            selectOption.append(
              $("<option>", {
                value: user.id,
                text: user.name
              })
            );
          });

          userlistField.append(selectOption).append(
            $("<input>", {
              type: "hidden",
              name: "batch_id",
              id: "batch_id",
              value: batchId,
              required: true
            })
          );
        } else {
          addStudentBatch.classList.add("d-none");
        }

        if (users.length > 0) {
          var table = $("<table>", {
            id: `users_batch_table[${batchId}]`,
            class: "table table-hover table-bordered text-center"
          }).append(
            $("<thead>").append(
              $("<tr>").append(
                $("<th>", { scope: "col", text: "Num." }),
                $("<th>", { scope: "col", text: "Name" }),
                $("<th>", { scope: "col", text: "Action" })
              )
            ),
            $("<tbody>")
          );

          $.each(users, function(index, user) {
            var row = $("<tr>").append(
              $("<td>", { text: index + 1 }),
              $("<td>", { text: user.name }),
              $("<td>").append(
                $("<div>", {
                  class:
                    "d-flex justify-content-center align-items-center act_btn_batch"
                }).append(
                  $("<button>", {
                    class: "btn btn-danger deleteStudentBatchBtn",
                    "data-batch_id": batchId,
                    "data-user_id": user.id
                  }).text("Delete")
                )
              )
            );
            table.find("tbody").append(row);
          });

          field.append(table);
          initializeDataTable(batchId);
        }
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
          title: "Error",
          text: errorMsg,
          icon: "error",
          confirmButtonText: "OK"
        });
      }
    });
  });

  // Add Student of Batch
  $("#addBatchStudent").on("submit", function(e) {
    e.preventDefault();

    var formDataArray = $(this).serializeArray();
    var batchId = formDataArray.find(item => item.name === "batch_id")?.value;

    $("#spinner_add_studbatch").removeClass("d-none");

    $.ajax({
      url: addUserBatchUrl,
      type: "POST",
      data: $(this).serialize(),
      success: function(data) {
        $("#spinner_add_studbatch").addClass("d-none");

        if (data.success) {
          Swal.fire({
            title: "Success",
            text: data.msg,
            icon: "success",
            confirmButtonText: "OK"
          })
            .then(result => {
              if (result.isConfirmed) {
                $.ajax({
                  url: showStudentsBatchUrl.replace(":batchId", batchId),
                  type: "GET",
                  success: function(response) {
                    var { users, users_ready } = response;

                    var userlistField = $(".user_list_option").empty();

                    if (users_ready.length) {
                      var addStudentBatch = document.getElementById(
                        "add_student_batch"
                      );
                      addStudentBatch.classList.remove("d-none");

                      var selectOption = $("<select>", {
                        name: "user_list[]",
                        id: "user_list_select",
                        class: "form-select fs-6",
                        multiple: true,
                        required: true
                      }).append(
                        $("<option>", {
                          value: "",
                          text: "-- select student --",
                          disabled: true,
                          selected: true
                        })
                      );

                      $.each(users_ready, function(_, user) {
                        selectOption.append(
                          $("<option>", {
                            value: user.id,
                            text: user.name
                          })
                        );
                      });

                      userlistField.append(selectOption).append(
                        $("<input>", {
                          type: "hidden",
                          name: "batch_id",
                          id: "batch_id",
                          value: batchId,
                          required: true
                        })
                      );
                    } else {
                      var addStudentBatch = document.getElementById(
                        "add_student_batch"
                      );
                      addStudentBatch.classList.add("d-none");
                    }

                    var tableField = $(".user_list_option_table").empty();

                    if (users.length) {
                      var table = $("<table>", {
                        id: `users_batch_table[${batchId}]`,
                        class: "table table-hover table-bordered text-center"
                      }).append(
                        $("<thead>").append(
                          $("<tr>").append(
                            $("<th>", { scope: "col", text: "Num." }),
                            $("<th>", { scope: "col", text: "Name" }),
                            $("<th>", { scope: "col", text: "Action" })
                          )
                        ),
                        $("<tbody>")
                      );

                      $.each(users, function(index, user) {
                        var row = $("<tr>").append(
                          $("<td>", { text: index + 1 }),
                          $("<td>", { text: user.name }),
                          $("<td>").append(
                            $("<div>", {
                              class:
                                "d-flex justify-content-center align-items-center act_btn_batch"
                            }).append(
                              $("<button>", {
                                class: "btn btn-danger deleteStudentBatchBtn",
                                "data-batch_id": batchId,
                                "data-user_id": user.id
                              }).text("Delete")
                            )
                          )
                        );
                        table.find("tbody").append(row);
                      });

                      tableField.append(table);
                      initializeDataTable(batchId);
                    }
                  },
                  error: function(xhr, status, error) {
                    Swal.fire({
                      title: "Failed!",
                      text: `Status: ${status} - Error: ${error} - Response: ${xhr.responseText}`,
                      icon: "error",
                      confirmButtonText: "OK"
                    });
                  }
                });
              }
            })
            .catch(err => {
              Swal.fire({
                title: "Failed!",
                text: err,
                icon: "error",
                confirmButtonText: "OK"
              });
            });
        } else {
          Swal.fire({
            title: "Failed!",
            text: data.msg,
            icon: "error",
            confirmButtonText: "OK"
          });
        }
      },
      error: function(xhr, status, error) {
        $("#spinner_add_studbatch").addClass("d-none");

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

  // Delete Student of Batch
  $(document).on("click", ".deleteStudentBatchBtn", function() {
    var batchId = $(this).data("batch_id");
    var userId = $(this).data("user_id");

    $.ajax({
      url: deleteStudentBatchUrl,
      method: "GET",
      data: { batch_id: batchId, user_id: userId },
      success: function(data) {
        if (data.success) {
          Swal.fire({
            title: "Success",
            text: data.msg,
            icon: "success",
            confirmButtonText: "OK"
          })
            .then(result => {
              if (result.isConfirmed) {
                $.ajax({
                  url: showStudentsBatchUrl.replace(":batchId", batchId),
                  type: "GET",
                  success: function(response) {
                    var { users, users_ready } = response;

                    var userlistField = $(".user_list_option").empty();

                    if (users_ready.length) {
                      var addStudentBatch = document.getElementById(
                        "add_student_batch"
                      );
                      addStudentBatch.classList.remove("d-none");

                      var selectOption = $("<select>", {
                        name: "user_list[]",
                        id: "user_list_select",
                        class: "form-select fs-6",
                        multiple: true,
                        required: true
                      }).append(
                        $("<option>", {
                          value: "",
                          text: "-- select student --",
                          disabled: true,
                          selected: true
                        })
                      );

                      $.each(users_ready, function(_, user) {
                        selectOption.append(
                          $("<option>", {
                            value: user.id,
                            text: user.name
                          })
                        );
                      });

                      userlistField.append(selectOption).append(
                        $("<input>", {
                          type: "hidden",
                          name: "batch_id",
                          id: "batch_id",
                          value: batchId,
                          required: true
                        })
                      );
                    } else {
                      var addStudentBatch = document.getElementById(
                        "add_student_batch"
                      );
                      addStudentBatch.classList.add("d-none");
                    }

                    var field = $(".user_list_option_table").empty();

                    if (users.length) {
                      var table = $("<table>", {
                        id: `users_batch_table[${batchId}]`,
                        class: "table table-hover table-bordered text-center"
                      }).append(
                        $("<thead>").append(
                          $("<tr>").append(
                            $("<th>", { scope: "col", text: "Num." }),
                            $("<th>", { scope: "col", text: "Name" }),
                            $("<th>", { scope: "col", text: "Action" })
                          )
                        ),
                        $("<tbody>")
                      );

                      $.each(users, function(index, user) {
                        var row = $("<tr>").append(
                          $("<td>", { text: index + 1 }),
                          $("<td>", { text: user.name }),
                          $("<td>").append(
                            $("<div>", {
                              class:
                                "d-flex justify-content-center align-items-center act_btn_batch"
                            }).append(
                              $("<button>", {
                                class: "btn btn-danger deleteStudentBatchBtn",
                                "data-batch_id": batchId,
                                "data-user_id": user.id
                              }).text("Delete")
                            )
                          )
                        );
                        table.find("tbody").append(row);
                      });

                      field.append(table);
                      initializeDataTable(batchId);
                    }
                  },
                  error: function(xhr, status, error) {
                    Swal.fire({
                      title: "Failed!",
                      text: `Status: ${status} - Error: ${error} - Response: ${xhr.responseText}`,
                      icon: "error",
                      confirmButtonText: "OK"
                    });
                  }
                });
              }
            })
            .catch(err => {
              Swal.fire({
                title: "Failed!",
                text: err,
                icon: "error",
                confirmButtonText: "OK"
              });
            });
        } else {
          Swal.fire({
            title: "Failed!",
            text: data.msg,
            icon: "error",
            confirmButtonText: "OK"
          });
        }
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
          title: "Error",
          text: errorMsg,
          icon: "error",
          confirmButtonText: "OK"
        });
      }
    });
  });
});
