<?php

use App\Http\Controllers\{
    AdminLecturerController,
    AuthController,
    ExamController,
    QuestionController,
    StudentController,
};
use Illuminate\Support\Facades\{
    Artisan,
    Route,
};

// Displays PHP information
Route::get("/info", function () {
    phpinfo();
});

// Creates storage link and redirects to home
Route::get("/link", function () {
    Artisan::call("storage:link");
    return redirect("/");
});

// Redirects login to home
Route::get("/login", function () {
    return redirect("/");
});

// Loads login page
Route::get("/", [AuthController::class, "loadLogin"])->name("login");

// Handles user login
Route::post("/login", [AuthController::class, "userLogin"])->name("userLogin");

// Logs out user
Route::get("/logout", [AuthController::class, "logout"])->name("logout");

// Loads forget password page
Route::get("/forget-password", [AuthController::class, "forgetPasswdLoad"])->name("forgetPasswdLoad");

// Handles forget password request
Route::post("/forget-password", [AuthController::class, "forgetPasswd"])->name("forgetPasswd");

// Loads reset password page with token
Route::get("/reset/{token}", [AuthController::class, "getReset"]);

// Handles password reset submission
Route::post("/reset_post/{token}", [AuthController::class, "postReset"])->name("reset_post");

// Verifies user email with token
Route::get("/verify/{token}", [AuthController::class, "verify"]);

// Admin routes with middleware and prefix
Route::group(["middleware" => ["auth", "admin"], "prefix" => "admin"], function () {
    // Admin dashboard
    Route::get("/dashboard", [AdminLecturerController::class, "adminLecturerDashboard"])->name("adminDashboard");

    // Batch management routes
    Route::group(["prefix" => "batches"], function () {
        Route::get("/", [AdminLecturerController::class, "adminLecturerBatches"])->name("adminBatches");
        Route::get("/get-students/{batchId}", [AdminLecturerController::class, "getBatchStudents"])->name("showAdminStudentsBatch");
        Route::get("/delete-student", [AdminLecturerController::class, "deleteBatchStudent"])->name("deleteAdminStudentBatch");
        Route::post("/add-batch", [AdminLecturerController::class, "addBatch"])->name("addAdminBatch");
        Route::post("/add-userbatch", [AdminLecturerController::class, "addUserBatch"])->name("addAdminUserBatch");
        Route::post("/delete-batch", [AdminLecturerController::class, "deleteBatch"])->name("deleteAdminBatch");
    });

    // Question management routes
    Route::group(["prefix" => "questions"], function () {
        // Listening questions
        Route::group(["prefix" => "listen"], function () {
            Route::get("/questions", [QuestionController::class, "adminLecturerListenQuestions"])->name("adminListenQuestions");
            Route::post("/add", [QuestionController::class, "addListeningQuest"])->name("addAdminListeningQuestion");
            Route::post("/update-audio", [QuestionController::class, "updateFileListening"])->name("updateAdminFileListening");
            Route::post("/add-opt-ans", [QuestionController::class, "addListenOptionAnswerManual"])->name("adminListenOptionAnswerManual");
            Route::post("/add-opt-ansfile", [QuestionController::class, "addListenOptionAnswerFile"])->name("adminListenOptionAnswerFile");
            Route::post("/edit-opt-ans", [QuestionController::class, "editListenOptionAnswer"])->name("editAdminListenOptionAnswer");
            Route::post("/delete-opt-ans", [QuestionController::class, "deleteListenOptionAnswer"])->name("deleteAdminListenOptionAnswer");
        });

        // SWE questions
        Route::group(["prefix" => "swe"], function () {
            Route::get("/questions", [QuestionController::class, "adminLecturerSWEQuestions"])->name("adminSWEQuestions");
            Route::post("/add", [QuestionController::class, "addSweQuestionManual"])->name("adminAddSweQuestionManual");
            Route::post("/addfile", [QuestionController::class, "addSweQuestionFile"])->name("adminAddSweQuestionFile");
            Route::post("/edit", [QuestionController::class, "editSweQuestion"])->name("adminEditSweQuestion");
            Route::post("/delete", [QuestionController::class, "deleteSweQuestion"])->name("adminDeleteSweQuestion");
        });

        // Reading questions
        Route::group(["prefix" => "reading"], function () {
            Route::get("/questions", [QuestionController::class, "adminLecturerReadingQuestions"])->name("adminReadingQuestions");
            Route::post("/add", [QuestionController::class, "addReadingQuestion"])->name("addAdminReadingQuestion");
            Route::post("/update-image", [QuestionController::class, "updateFileReading"])->name("updateAdminFileReading");
            Route::post("/add-quest", [QuestionController::class, "addReadingQuest"])->name("adminReadingQuest");
            Route::post("/add-questfile", [QuestionController::class, "addReadingQuestFile"])->name("adminReadingQuestFile");
            Route::post("/edit-quest", [QuestionController::class, "editReadingQuest"])->name("editAdminReadingQuest");
            Route::post("/delete-quest", [QuestionController::class, "deleteReadingQuest"])->name("deleteAdminReadingQuest");
        });
    });

    // Exam management routes
    Route::group(["prefix" => "exams"], function () {
        Route::get("/", [AdminLecturerController::class, "examDashboard"])->name("adminExams");
        Route::post("/add", [AdminLecturerController::class, "addExam"])->name("addAdminExam");
        Route::post("/update", [AdminLecturerController::class, "updateExam"])->name("updateAdminExam");
        Route::post("/delete", [AdminLecturerController::class, "deleteExam"])->name("deleteAdminExam");
    });

    // Exam result routes
    Route::group(["prefix" => "exam-result"], function () {
        Route::get("/", [AdminLecturerController::class, "examResultsDashboard"])->name("adminExamResults");
        Route::get("/get-exam-result/{studentId}/{batchId}", [AdminLecturerController::class, "getExamResultStudent"])->name("showAdminStudentsExamResult");
        Route::post("/delete", [AdminLecturerController::class, "deleteExamResult"])->name("deleteAdminExamResult");
    });

    // User management routes
    Route::group(["prefix" => "users"], function () {
        Route::get("/", [AdminLecturerController::class, "adminLecturerUsers"])->name("adminUsers");
        Route::post("/add", [AdminLecturerController::class, "addUser"])->name("addAdminUser");
        Route::post("/edit", [AdminLecturerController::class, "editUser"])->name("editAdminUser");
        Route::post("/delete", [AdminLecturerController::class, "deleteUser"])->name("deleteAdminUser");
        Route::get("/export", [AdminLecturerController::class, "exportUsers"])->name("exportAdminUsers");
        Route::post("/resendVerify", [AdminLecturerController::class, "resendVerify"])->name("resendAdminVerify");
    });

    // Settings routes
    Route::group(["prefix" => "settings"], function () {
        Route::get("/", [AdminLecturerController::class, "settingsDashboard"])->name("adminSettings");
        Route::post("/updateprofile", [AdminLecturerController::class, "updateAdminLecturerProfile"])->name("updateAdminProfile");
        Route::post("/updatepassword", [AdminLecturerController::class, "updateAdminLecturerPassword"])->name("updateAdminPassword");
    });
});

// Lecturer routes with middleware and prefix
Route::group(["middleware" => ["auth", "lecturer"], "prefix" => "lecturer"], function () {
    // Lecturer dashboard
    Route::get("/dashboard", [AdminLecturerController::class, "adminLecturerDashboard"])->name("lecturerDashboard");

    // Batch management routes
    Route::group(["prefix" => "batches"], function () {
        Route::get("/", [AdminLecturerController::class, "adminLecturerBatches"])->name("lecturerBatches");
        Route::get("/get-students/{batchId}", [AdminLecturerController::class, "getBatchStudents"])->name("showLecturerStudentsBatch");
        Route::get("/delete-student", [AdminLecturerController::class, "deleteBatchStudent"])->name("deleteLecturerStudentBatch");
        Route::post("/add-batch", [AdminLecturerController::class, "addBatch"])->name("addLecturerBatch");
        Route::post("/add-userbatch", [AdminLecturerController::class, "addUserBatch"])->name("addLecturerUserBatch");
        Route::post("/delete-batch", [AdminLecturerController::class, "deleteBatch"])->name("deleteLecturerBatch");
    });

    // Question management routes
    Route::group(["prefix" => "questions"], function () {
        // Listening questions
        Route::group(["prefix" => "listen"], function () {
            Route::get("/questions", [QuestionController::class, "adminLecturerListenQuestions"])->name("lecturerListenQuestions");
            Route::post("/add", [QuestionController::class, "addListeningQuest"])->name("addLecturerListeningQuestion");
            Route::post("/update-audio", [QuestionController::class, "updateFileListening"])->name("updateLecturerFileListening");
            Route::post("/add-opt-ans", [QuestionController::class, "addListenOptionAnswerManual"])->name("lecturerListenOptionAnswerManual");
            Route::post("/add-opt-ansfile", [QuestionController::class, "addListenOptionAnswerFile"])->name("lecturerListenOptionAnswerFile");
            Route::post("/edit-opt-ans", [QuestionController::class, "editListenOptionAnswer"])->name("editLecturerListenOptionAnswer");
            Route::post("/delete-opt-ans", [QuestionController::class, "deleteListenOptionAnswer"])->name("deleteLecturerListenOptionAnswer");
        });

        // SWE questions
        Route::group(["prefix" => "swe"], function () {
            Route::get("/questions", [QuestionController::class, "adminLecturerSWEQuestions"])->name("lecturerSWEQuestions");
            Route::post("/add", [QuestionController::class, "addSweQuestionManual"])->name("lecturerAddSweQuestionManual");
            Route::post("/addfile", [QuestionController::class, "addSweQuestionFile"])->name("lecturerAddSweQuestionFile");
            Route::post("/edit", [QuestionController::class, "editSweQuestion"])->name("lecturerEditSweQuestion");
            Route::post("/delete", [QuestionController::class, "deleteSweQuestion"])->name("lecturerDeleteSweQuestion");
        });

        // Reading questions
        Route::group(["prefix" => "reading"], function () {
            Route::get("/questions", [QuestionController::class, "adminLecturerReadingQuestions"])->name("lecturerReadingQuestions");
            Route::post("/add", [QuestionController::class, "addReadingQuestion"])->name("addLecturerReadingQuestion");
            Route::post("/update-image", [QuestionController::class, "updateFileReading"])->name("updateLecturerFileReading");
            Route::post("/add-quest", [QuestionController::class, "addReadingQuest"])->name("lecturerReadingQuest");
            Route::post("/add-questfile", [QuestionController::class, "addReadingQuestFile"])->name("lecturerReadingQuestFile");
            Route::post("/edit-quest", [QuestionController::class, "editReadingQuest"])->name("editLecturerReadingQuest");
            Route::post("/delete-quest", [QuestionController::class, "deleteReadingQuest"])->name("deleteLecturerReadingQuest");
        });
    });

    // Exam management routes
    Route::group(["prefix" => "exams"], function () {
        Route::get("/", [AdminLecturerController::class, "examDashboard"])->name("lecturerExams");
        Route::post("/add", [AdminLecturerController::class, "addExam"])->name("addLecturerExam");
        Route::post("/update", [AdminLecturerController::class, "updateExam"])->name("updateLecturerExam");
        Route::post("/delete", [AdminLecturerController::class, "deleteExam"])->name("deleteLecturerExam");
    });

    // Exam result routes
    Route::group(["prefix" => "exam-result"], function () {
        Route::get("/", [AdminLecturerController::class, "examResultsDashboard"])->name("lecturerExamResults");
        Route::get("/get-exam-result/{studentId}/{batchId}", [AdminLecturerController::class, "getExamResultStudent"])->name("showLecturerStudentsExamResult");
        Route::post("/delete", [AdminLecturerController::class, "deleteExamResult"])->name("deleteLecturerExamResult");
    });

    // User management routes
    Route::group(["prefix" => "users"], function () {
        Route::get("/", [AdminLecturerController::class, "adminLecturerUsers"])->name("lecturerUsers");
        Route::post("/add", [AdminLecturerController::class, "addUser"])->name("addLecturerUser");
        Route::post("/edit", [AdminLecturerController::class, "editUser"])->name("editLecturerUser");
        Route::post("/delete", [AdminLecturerController::class, "deleteUser"])->name("deleteLecturerUser");
        Route::get("/export", [AdminLecturerController::class, "exportUsers"])->name("exportLecturerUsers");
        Route::post("/resendVerify", [AdminLecturerController::class, "resendVerify"])->name("resendLecturerVerify");
    });

    // Settings routes
    Route::group(["prefix" => "settings"], function () {
        Route::get("/", [AdminLecturerController::class, "settingsDashboard"])->name("lecturerSettings");
        Route::post("/updateprofile", [AdminLecturerController::class, "updateAdminLecturerProfile"])->name("updateLecturerProfile");
        Route::post("/updatepassword", [AdminLecturerController::class, "updateAdminLecturerPassword"])->name("updateLecturerPassword");
    });
});

// Student routes with middleware and prefix
Route::group(["middleware" => ["auth", "student"], "prefix" => "student"], function () {
    // Exam management routes
    Route::group(["prefix" => "exams"], function () {
        Route::get("/", [StudentController::class, "studentExams"])->name("studentExams");
        Route::get("/exam/{exam_id}/{user_id}/{token}", [StudentController::class, "showExam"])->name("showExam");
        Route::post("/exam/submit", [StudentController::class, "submitExam"])->name("submitExam");
        Route::post("/exam/usfl", [StudentController::class, "updateStatusFileListening"])->name("updateStatusFileListening");
        Route::post("/exam/saveupdate-remaining-time", [ExamController::class, "saveOrUpdateRemainingTime"])->name("saveOrUpdateRemainingTime");
        Route::post("/exam/update-current-part", [ExamController::class, "updateCurrentPart"])->name("updateCurrentPart");
        Route::post("/exam/update-exam-session", [StudentController::class, "updateExamSession"])->name("updateExamSession");
    });

    // Exam result route
    Route::get("/exam-result", [StudentController::class, "studentExamResult"])->name("studentExamResult");

    // Settings routes
    Route::group(["prefix" => "settings"], function () {
        Route::get("/", [StudentController::class, "settingsDashboard"])->name("studentSettings");
        Route::post("/updateprofile", [StudentController::class, "updateStudentProfile"])->name("updateStudentProfile");
        Route::post("/updatepassword", [StudentController::class, "updateStudentPassword"])->name("updateStudentPassword");
    });
});
