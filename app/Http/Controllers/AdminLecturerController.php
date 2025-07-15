<?php

namespace App\Http\Controllers;

use App\Exports\{
    ExportUsers,
    ExportStudents,
};
use App\Models\{
    Batch,
    Category,
    Exam,
    ExamSession,
    ExamTimer,
    ListeningQuestion,
    ProfileDetail,
    ReadingQuestion,
    SweQuestion,
    User,
    UserExamLink,
};
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    Auth,
    DB,
    File,
    Hash,
    Storage,
    URL,
    Validator,
};
use Illuminate\Validation\{
    Rule,
    ValidationException,
};
use Maatwebsite\Excel\Exceptions\LaravelExcelException;
use Maatwebsite\Excel\Facades\Excel;

class AdminLecturerController extends Controller
{
    // Function to calculate the final score based on total score and category count
    private function finalScore($totalScore, $categoryCount)
    {
        return $categoryCount > 0 ? round($totalScore * 10 / $categoryCount) : 0;
    }

    // Function to handle the admin lecturer dashboard
    public function adminLecturerDashboard()
    {
        try {
            DB::beginTransaction();
            $title = "Dashboard | " . config("app.name");
            $user = Auth::user();
            $isAdmin = $user->is_admin;

            if ($isAdmin == 1) {
                $total_user = User::count();
            } elseif ($isAdmin == 2) {
                $total_user = User::whereIn("is_admin", [2, 0])->count();
            }

            $totals = [
                "total_batch" => $total_batch = Batch::count(),
                "total_listen_question" => $total_listen_question = ListeningQuestion::count(),
                "total_swe_question" => $total_swe_question = SweQuestion::count(),
                "total_reading_question" => $total_reading_question = ReadingQuestion::count(),
                "total_exam" => $total_exam = Exam::count(),
                "total_user" => $total_user
            ];
            $batches = Batch::with([
                "exams",
                "users" => function ($query) {
                    $query->where("is_admin", 0);
                }
            ])->paginate(1);
            $batchData = $batches->items();
            $mappedData = collect($batchData)->map(function ($batch) {
                return $batch->exams->map(function ($exam) use ($batch) {
                    return [
                        "exam_name" => $exam->exam_name,
                        "total_users" => $batch->users->count(),
                        "exam_date" => $exam->exam_date
                    ];
                });
            })->flatten(1);
            $paginatedData = new LengthAwarePaginator($mappedData, $batches->total(), $batches->perPage(), $batches->currentPage(), [
                "path" => request()->url(),
                "query" => request()->query()
            ]);

            DB::commit();

            return view("admin_lecturer.dashboard", compact("title", "totals", "paginatedData"));
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A required model was not found. Please check your data and try again."
            ], 404);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A database error occurred. Please check your query or constraints."
            ], 500);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "An unexpected error occurred. Please try again later."
            ], 500);
        }
    }

    // Function to handle the admin lecturer batches
    public function adminLecturerBatches()
    {
        try {
            DB::beginTransaction();

            $title = "Batches | " . config("app.name");
            $batches = Batch::select("id", "name")->paginate(5);
            $users_verified = User::where("is_admin", 0)->whereNotNull("email_verified_at")->get();

            DB::commit();

            return view("admin_lecturer.batches", compact("title", "batches", "users_verified"));
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A required model was not found. Please check your data and try again."
            ], 404);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A database error occurred. Please check your query or constraints."
            ], 500);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "An unexpected error occurred. Please try again later."
            ], 500);
        }
    }

    // Function to add a new batch
    public function addBatch(Request $request)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validate([
                "batch_name" => [
                    "required",
                    "string",
                    "max:255",
                    Rule::unique("batches", "name")->ignore($request->id),
                    function ($attribute, $value, $fail) use ($request) {
                        if (strpos($value, "Batch") == 0) {
                            if (!preg_match("/^Batch\s\d+/i", $value)) {
                                $fail("The batch name must start with \"Batch\" followed by a number.");
                            }
                        } else {
                            $fail("The batch name must start with the word \"Batch\".");
                        }
                    }
                ],
                "user_ids" => "nullable|array",
                "user_ids.*" => "nullable|exists:users,id"
            ], [
                "batch_name.required" => "Batch name is required.",
                "batch_name.string" => "Batch name must be a string.",
                "batch_name.max" => "Batch name may not be greater than 255 characters.",
                "batch_name.unique" => "The batch name has already been taken."
            ]);
            $batch = new Batch();
            $batch->name = $validatedData["batch_name"];
            $batch->save();
            $categories = Category::all();
            $category_ids = $categories->pluck("id")->toArray();

            if (!empty($validatedData["user_ids"])) {
                $batch->users()->attach($validatedData["user_ids"]);
            }

            $batch->categories()->attach($category_ids);

            DB::commit();

            return response()->json([
                "success" => true,
                "msg" => "Batch with student added successfully."
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json([
                "success" => false,
                "msg" => $errorMsg
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A required model was not found. Please check your data and try again."
            ]);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A database error occurred. Please check your query or constraints."
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "An unexpected error occurred. Please try again later."
            ]);
        }
    }

    // Function to get students for a specific batch
    public function getBatchStudents($batchId)
    {
        try {
            DB::beginTransaction();

            $users_ready = User::select("users.id", "users.name")
                ->where("users.is_admin", 0)
                ->whereNotNull("users.email_verified_at")
                ->whereDoesntHave("batches", function ($query) use ($batchId) {
                    $query->where("batches.id", $batchId);
                })->get();
            $users = User::select("id", "name")
                ->whereHas("batches", function ($query) use ($batchId) {
                    $query->where("batch_id", $batchId);
                })
                ->where("is_admin", 0)
                ->whereNotNull("email_verified_at")
                ->get();

            DB::commit();

            return response()->json([
                "success" => true,
                "users_ready" => $users_ready,
                "users" => $users
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A required model was not found. Please check your data and try again."
            ]);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A database error occurred. Please check your query or constraints."
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "An unexpected error occurred. Please try again later."
            ]);
        }
    }

    // Function to add a user to a batch
    public function addUserBatch(Request $request)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validate([
                "batch_id" => "required",
                "user_list" => "required|array",
                "user_list.*" => "exists:users,id"
            ], [
                "batch_id.required" => "Batch ID is required.",
                "user_list.required" => "User list is required.",
                "user_list.array" => "User list must be an array.",
                "user_list.*.exists" => "One or more users do not exist."
            ]);
            $batch = Batch::findOrFail($validatedData["batch_id"]);
            $batch->users()->attach($validatedData["user_list"]);

            DB::commit();

            return response()->json([
                "success" => true,
                "msg" => "User of Batch added successfully."
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json([
                "success" => false,
                "msg" => $errorMsg
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A required model was not found. Please check your data and try again."
            ]);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A database error occurred. Please check your query or constraints."
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "An unexpected error occurred. Please try again later."
            ]);
        }
    }

    // Function to delete a user from a batch
    public function deleteBatchStudent(Request $request)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validate([
                "batch_id" => "required",
                "user_id" => "required|exists:users,id"
            ], [
                "batch_id.required" => "Batch ID is required.",
                "user_id.required" => "User ID is required.",
                "user_id.exists" => "The selected user does not exist."
            ]);
            $batch = Batch::findOrFail($validatedData["batch_id"]);
            $batch->users()->detach([$validatedData["user_id"]]);

            UserExamLink::where("user_id", $validatedData["user_id"])->delete();

            DB::commit();

            return response()->json([
                "success" => true,
                "msg" => "User of Batch has been removed."
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json([
                "success" => false,
                "msg" => $errorMsg
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A required model was not found. Please check your data and try again."
            ]);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A database error occurred. Please check your query or constraints."
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "An unexpected error occurred. Please try again later."
            ]);
        }
    }

    // Function to delete a batch
    public function deleteBatch(Request $request)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validate([
                "id" => "|exists:batches,id"
            ], [
                "id.required" => "ID is required.",
                "id.integer" => "ID must be an integer.",
                "id.exists" => "The selected ID does not exist in batches."
            ]);
            $batch = Batch::findOrFail($validatedData["id"]);
            $batch_name = $batch->name;
            $exams = $batch->exams;
            $users = $batch->users;
            $fileAudioContentsOld = str_replace(" ", "_", $batch_name);
            $fileImageContentsOld = str_replace(" ", "_", $batch_name);
            $storageAudioPath = "audios/{$fileAudioContentsOld}";
            $storageImagePath = "images/{$fileAudioContentsOld}";

            if (Storage::disk("local")->exists($storageAudioPath)) {
                Storage::disk("local")->deleteDirectory($storageAudioPath);
            }

            if (Storage::disk("local")->exists($storageImagePath)) {
                Storage::disk("local")->deleteDirectory($storageImagePath);
            }

            $base_directory = storage_path("app/public");
            $directory = $base_directory . "/user_" . Auth::id() . "/" . str_replace(" ", "_", $batch_name);

            if (File::exists($directory)) {
                File::deleteDirectory($directory);
            }

            foreach ($users as $user) {
                $user_directory = $base_directory . "/user_" . $user->id . "/" . str_replace(" ", "_", $batch_name);

                if (File::exists($user_directory)) {
                    File::deleteDirectory($user_directory);
                }
            }

            $batch->categories()->detach();
            $batch->users()->detach();
            $batch->fileListenings()->each(function ($fileListening) use ($batch) {
                $fileListening->categories()->detach();
                $fileListening->batches()->detach();
                $fileListening->questions()->each(function ($question) use ($fileListening) {
                    $question->fileListenings()->detach($fileListening->id);

                    if ($question->fileListenings()->count() == 0) {
                        $question->delete();
                    }
                });

                if ($fileListening->batches()->count() == 0) {
                    $fileListening->delete();
                }
            });
            $batch->sweQuestions()->each(function ($sweQuestion) use ($batch) {
                $sweQuestion->categories()->detach();
                $sweQuestion->batches()->detach();

                if ($sweQuestion->batches()->count() == 0) {
                    $sweQuestion->delete();
                }
            });
            $batch->readings()->each(function ($reading) use ($batch) {
                $reading->categories()->detach();
                $reading->batches()->detach();
                $reading->questions()->each(function ($question) use ($reading) {
                    $question->readings()->detach($reading->id);

                    if ($question->readings()->count() == 0) {
                        $question->delete();
                    }
                });

                if ($reading->batches()->count() == 0) {
                    $reading->delete();
                }
            });
            $exams->each(function ($exam) {
                $exam->categories()->detach();
                $exam->batches()->detach();

                if ($exam->batches()->count() == 0) {
                    $exam->delete();
                }
            });
            $batch->delete();

            DB::commit();

            return response()->json([
                "success" => true,
                "msg" => "Batch deleted successfully."
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json([
                "success" => false,
                "msg" => $errorMsg
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A required model was not found. Please check your data and try again."
            ]);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A database error occurred. Please check your query or constraints."
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "An unexpected error occurred. Please try again later."
            ]);
        }
    }

    // Function to display the exam dashboard
    public function examDashboard()
    {
        try {
            DB::beginTransaction();

            $title = "Exams | " . config("app.name");
            $batches = Batch::select("id", "name")->orWhereDoesntHave("exams")->get();
            $batchExam = Batch::select("id", "name")->has("exams")->paginate(1);
            $exams = Exam::select("id", "exam_name", "exam_date", "exam_time", "exam_attempt")->get();

            DB::commit();

            return view("admin_lecturer.exam_dashboard", compact("title", "batches", "batchExam", "exams"));
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A required model was not found. Please check your data and try again."
            ], 404);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A database error occurred. Please check your query or constraints."
            ], 500);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "An unexpected error occurred. Please try again later."
            ], 500);
        }
    }

    // Function to add an exam
    public function addExam(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                "batch_id" => "required|integer|exists:batches,id",
                "date" => "required|date_format:Y-m-d"
            ], [
                "batch_id.required" => "Batch ID is required.",
                "batch_id.integer" => "Batch ID must be an integer.",
                "batch_id.exists" => "The selected batch does not exist.",
                "date.required" => "Date is required.",
                "date.date_format" => "Date must be in the format Year-Month-Day."
            ]);
            $exam_times = [
                "Listening" => "00:35",
                "Structure" => "00:25",
                "Reading" => "00:55"
            ];
            $exam_attempt = 1;
            $exam_date = $validated["date"];
            $batch = Batch::findOrFail($validated["batch_id"]);
            $batch_name = $batch->name;
            $category_id = 1;

            foreach ($exam_times as $et => $exam_time) {
                $exam_name = "TOEFL " . $batch_name . " - " . $et;
                $exam = Exam::create([
                    "exam_name" => $exam_name,
                    "exam_date" => $exam_date,
                    "exam_time" => $exam_time,
                    "exam_attempt" => $exam_attempt
                ]);
                $batch->exams()->attach($exam->id, ["category_id" => $category_id]);
                $users = $batch->users;

                foreach ($users as $user) {
                    $token = hash("sha256", Str::random(40) . $exam->id . $user->id);
                    $link = URL::to("/student/exams/exam/" . $exam->id . "/" . $user->id . "/" . $token);

                    DB::table("user_exam_links")->insert([
                        "user_id" => $user->id,
                        "exam_id" => $exam->id,
                        "link" => $link,
                        "token" => $token,
                        "created_at" => now(),
                        "updated_at" => now()
                    ]);
                }
                $category_id++;
            }

            DB::commit();

            return response()->json([
                "success" => true,
                "msg" => "Exam added successfully."
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json([
                "success" => false,
                "msg" => $errorMsg
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A required model was not found. Please check your data and try again."
            ]);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A database error occurred. Please check your query or constraints."
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "An unexpected error occurred. Please try again later."
            ]);
        }
    }

    // Function to update an exam
    public function updateExam(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                "exam_id" => "required|integer|exists:exams,id",
                "date" => "required|date_format:Y-m-d",
                "time" => "required|string",
                "attempt" => "required|integer"
            ], [
                "exam_id.required" => "Exam ID is required.",
                "exam_id.integer" => "Exam ID must be an integer.",
                "exam_id.exists" => "The selected exam does not exist.",
                "date.required" => "Date is required.",
                "date.date_format" => "Date must be in the format YYYY-MM-DD.",
                "time.required" => "Time is required.",
                "time.string" => "Time must be a string.",
                "attempt.required" => "Attempt is required.",
                "attempt.integer" => "Attempt must be an integer."
            ]);
            $exam_id = $validated["exam_id"];
            $exam_date = $validated["date"];
            $exam_time = $validated["time"];
            $exam_attempt = $validated["attempt"];
            $exam = Exam::find($exam_id);

            if ($exam) {
                $exam->exam_date = $exam_date;
                $exam->exam_time = $exam_time;
                $exam->exam_attempt = $exam_attempt;
                $exam->save();

                DB::commit();

                return response()->json([
                    "success" => true,
                    "msg" => "Exam updated successfully."
                ]);
            } else {
                return response()->json([
                    "success" => false,
                    "msg" => "Exam not found"
                ]);
            }
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json([
                "success" => false,
                "msg" => $errorMsg
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A required model was not found. Please check your data and try again."
            ]);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A database error occurred. Please check your query or constraints."
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "An unexpected error occurred. Please try again later."
            ]);
        }
    }

    // Function to delete an exam
    public function deleteExam(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                "exam_id" => "required|integer|exists:exams,id"
            ], [
                "exam_id.required" => "Exam ID is required.",
                "exam_id.integer" => "Exam ID must be an integer.",
                "exam_id.exists" => "The selected exam does not exist."
            ]);
            $exam = Exam::where("id", $validated["exam_id"]);
            $exam->delete();

            DB::commit();

            return response()->json([
                "success" => true,
                "msg" => "Exam deleted successfully."
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json([
                "success" => false,
                "msg" => $errorMsg
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A required model was not found. Please check your data and try again."
            ]);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A database error occurred. Please check your query or constraints."
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "An unexpected error occurred. Please try again later."
            ]);
        }
    }

    // Function to display the exam results dashboard
    public function examResultsDashboard()
    {
        try {
            DB::beginTransaction();

            $title = "Exam Results | " . config("app.name");
            $batches = Batch::with([
                "users" => function ($query) {
                    $query->where("is_admin", 0);
                },
                "exams.examSessions"
            ])->paginate(1);

            DB::commit();

            return view("admin_lecturer.exam_results", compact("title", "batches"));
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A required model was not found. Please check your data and try again."
            ], 404);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A database error occurred. Please check your query or constraints."
            ], 500);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "An unexpected error occurred. Please try again later."
            ], 500);
        }
    }

    // Function to get exam results for a specific student in a batch
    public function getExamResultStudent($studentId, $batchId)
    {
        try {
            DB::beginTransaction();

            $examSessions = ExamSession::where("user_id", $studentId)->whereHas("exam", function ($query) use ($batchId) {
                $query->whereHas("batches", function ($batchQuery) use ($batchId) {
                    $batchQuery->where("batch_id", $batchId);
                });
            })->get();

            if ($examSessions->isEmpty()) {
                return response()->json([
                    "success" => false,
                    "msg" => "No exam results found for this student in the selected batch."
                ]);
            }

            $examCategories = ["Listening", "Structure", "Reading"];
            $examResults = [];
            $msg = null;
            $categoriesNotTaken = array_fill_keys($examCategories, true);

            foreach ($examSessions as $session) {
                $exam_name = Exam::where("id", $session->exam_id)->pluck("exam_name")->first();
                $exam_category = explode(" ", $exam_name)[4];

                if (in_array($exam_category, $examCategories)) {
                    $examResults[$exam_category] = $session->score;
                    $categoriesNotTaken[$exam_category] = false;
                } else {
                    return response()->json([
                        "success" => false,
                        "msg" => "Category is not available."
                    ]);
                }
            }

            $notTakenCategories = array_keys(array_filter($categoriesNotTaken));

            if (!empty($notTakenCategories)) {
                $notTakenCategoriesList = implode(", ", $notTakenCategories);

                if ($msg == null) {
                    $msg = "The user has not yet taken the following tests: {$notTakenCategoriesList}.";
                } else {
                    $msg .= "<br>The user has not yet taken the following tests: {$notTakenCategoriesList}.";
                }
            }

            $total_score = array_sum($examResults);
            $final_score = $this->finalScore($total_score, 3);
            $examResults["final_score"] = $final_score;

            DB::commit();

            return response()->json([
                "success" => true,
                "exam_results" => $examResults,
                "exam_msg" => $msg
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A required model was not found. Please check your data and try again."
            ]);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A database error occurred. Please check your query or constraints."
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "An unexpected error occurred. Please try again later."
            ]);
        }
    }

    // Function to delete an exam result
    public function deleteExamResult(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                "id" => "required|integer|exists:batches,id",
                "user_id" => "required|integer|exists:users,id"
            ], [
                "id.required" => "ID is required.",
                "id.integer" => "ID must be an integer.",
                "id.exists" => "The selected batch does not exist.",
                "user_id.required" => "User ID is required.",
                "user_id.integer" => "User ID must be an integer.",
                "user_id.exists" => "The selected user does not exist."
            ]);
            $batchId = $validated["id"];
            $userId = $validated["user_id"];
            $examId = ExamSession::where("user_id", $userId)->whereHas("exam", function ($query) use ($batchId) {
                $query->whereHas("batches", function ($batchQuery) use ($batchId) {
                    $batchQuery->where("batch_id", $batchId);
                });
            })->pluck("exam_id")->first();

            if (!$examId) {
                return response()->json([
                    "success" => false,
                    "msg" => "Exam session not found for the provided user and batch."
                ]);
            }

            $fileListeningIds = DB::table("batch_category_file_listenings")
                ->join("file_listenings", "batch_category_file_listenings.file_listening_id", "=", "file_listenings.id")
                ->join("batches", "batch_category_file_listenings.batch_id", "=", "batches.id")
                ->where("batches.id", $batchId)
                ->pluck("file_listenings.id");

            DB::table("user_audio_plays")
                ->where("user_id", $userId)
                ->whereIn("file_listening_id", $fileListeningIds)
                ->update(["status_played" => 0]);

            UserExamLink::where("user_id", $userId)->whereHas("exam", function ($query) use ($batchId) {
                $query->whereHas("batches", function ($batchQuery) use ($batchId) {
                    $batchQuery->where("batch_id", $batchId);
                });
            })->update(["current_part" => null]);

            ExamSession::where("user_id", $userId)->whereHas("exam", function ($query) use ($batchId) {
                $query->whereHas("batches", function ($batchQuery) use ($batchId) {
                    $batchQuery->where("batch_id", $batchId);
                });
            })->delete();

            ExamTimer::where("user_id", $userId)->whereHas("exam", function ($query) use ($batchId) {
                $query->whereHas("batches", function ($batchQuery) use ($batchId) {
                    $batchQuery->where("batch_id", $batchId);
                });
            })->delete();

            DB::commit();

            return response()->json([
                "success" => true,
                "msg" => "Exam Result deleted successfully."
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json([
                "success" => false,
                "msg" => $errorMsg
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A required model was not found. Please check your data and try again."
            ]);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "A database error occurred. Please check your query or constraints."
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "An unexpected error occurred. Please try again later."
            ]);
        }
    }

    // Function to display the lecturer users dashboard
    public function adminLecturerUsers()
    {
        try {
            DB::beginTransaction();

            $title = "Users | " . config("app.name");
            $curr_user = Auth::user();

            if (!$curr_user) {
                abort(403, "Unauthorized");
            }

            if ($curr_user->is_admin == 1) {
                $users = User::all();
            } elseif ($curr_user->is_admin == 2) {
                $users = User::whereIn("is_admin", [2, 0])->get();
            } else {
                $users = collect();
            }

            DB::commit();

            return view("admin_lecturer.users_dashboard", compact("title", "users"));
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "A required model was not found. Please check your data and try again."], 404);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "A database error occurred. Please check your query or constraints."], 500);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "An unexpected error occurred. Please try again later."], 500);
        }
    }

    // Function to add a new user
    public function addUser(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                "name" => "required|string|max:255",
                "email" => "required|email|unique:users,email",
                "user_role" => "required|integer"
            ], [
                "name.required" => "Name is required.",
                "name.string" => "Name must be a string.",
                "name.max" => "Name may not be greater than 255 characters.",
                "email.required" => "Email is required.",
                "email.email" => "Email must be a valid email address.",
                "email.unique" => "The email has already been taken.",
                "user_role.required" => "User role is required.",
                "user_role.integer" => "User role must be an integer."
            ]);

            if (User::where("email", $validated["email"])->exists()) {
                return response()->json(["success" => false, "msg" => "Email has been registered. Please use another email."]);
            }

            $allowedDomains = [0 => "@student.unaki.ac.id", 2 => "@unaki.ac.id"];
            $domain = $allowedDomains[$validated["user_role"]] ?? null;

            if ($domain && !str_ends_with($validated["email"], $domain)) {
                $msg = $validated["user_role"] == 0 ? "Only email from student.unaki.ac.id domain are allowed." : "Only email from unaki.ac.id domain are allowed.";

                return response()->json(["success" => false, "msg" => $msg]);
            }

            $passwd = explode("@", $validated["email"])[0];
            $passwd = $validated["user_role"] == 1 || $validated["user_role"] == 2 ? "11111111" : ($validated["user_role"] == 0 ? $passwd : '');
            $is_admin = in_array($validated["user_role"], [1, 2]) ? $validated["user_role"] : 0;
            $remember_token = Str::random(10);
            $password_line = $passwd ? "Password: {$passwd}<br>" : '';
            $data = "
                <b>Hi {$validated["name"]},</b><br><br>
                Your profile has been successfully added. Here are your account details:<br><br>
                Name: {$validated["name"]}<br>
                Email: {$validated["email"]}<br>
                {$password_line}<br><br>
                Please verify your account so you can take part in the TOEFL UNAKI.<br><br>
                <a href='" . url("verify/{$remember_token}") . "' style='border: 10px solid blue; border-radius: 5px; background-color: blue; color: white; text-decoration: none;'>Verification</a><br><br><br><br>
                Regards,<br><br><br>" . config("app.name") . "
            ";

            (new SendMailController())->sendMail($validated["email"], "User Account | " . config("app.name"), $data);

            $user = User::create([
                "name" => $validated["name"],
                "email" => $validated["email"],
                "is_admin" => $is_admin,
                "password" => Hash::make($passwd),
                "remember_token" => $remember_token
            ]);

            ProfileDetail::create(["user_id" => $user->id]);

            DB::commit();

            return response()->json(["success" => true, "msg" => "User added successfully. Please contact the user to verify the account."]);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json(["success" => false, "msg" => $errorMsg]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "A required model was not found. Please check your data and try again."]);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "A database error occurred. Please check your query or constraints."]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "An unexpected error occurred. Please try again later."]);
        }
    }

    // Function to resend verification email
    public function resendVerify(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                "id" => "required|exists:users,id"
            ], [
                "id.required" => "User ID is required.",
                "id.exists" => "The selected user does not exist."
            ]);
            $user = User::findOrFail($validated["id"]);
            $passwd = explode("@", $user->email)[0];
            $user->remember_token = Str::random(40);
            $user->password = Hash::make($passwd);
            $user->save();
            $data = "
                <b>Hi {$user->name},</b><br><br>
                Please verify your account.<br><br>
                Email: {$user->email}<br>
                Password: {$passwd}<br><br><br>
                <a href='" . url("verify/{$user->remember_token}") . "' style='border: 10px solid blue; border-radius: 5px; background-color: blue; color: white; text-decoration: none;'>Verification</a><br><br><br>
                Thanks,<br><br>" . config("app.name");

            (new SendMailController())->sendMail($user->email, "Verify Account | " . config("app.name"), $data);

            DB::commit();

            return response()->json(["success" => true, "msg" => "Verification email sending successfully."]);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json(["success" => false, "msg" => $errorMsg]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "A required model was not found. Please check your data and try again."]);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "A database error occurred. Please check your query or constraints."]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "An unexpected error occurred. Please try again later."]);
        }
    }

    // Function to edit user details
    public function editUser(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                "id" => "required|exists:users,id"
            ], [
                "id.required" => "User ID is required.",
                "id.exists" => "The selected user does not exist."
            ]);
            $user = User::findOrFail($validated["id"]);
            $user->update($request->only("name", "email"));

            DB::commit();

            return response()->json(["success" => true, "msg" => "User updated successfully."]);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json(["success" => false, "msg" => $errorMsg]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "A required model was not found. Please check your data and try again."]);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "A database error occurred. Please check your query or constraints."]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "An unexpected error occurred. Please try again later."]);
        }
    }

    // Function to delete a user
    public function deleteUser(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                "id" => "required|exists:users,id"
            ], [
                "id.required" => "User ID is required.",
                "id.exists" => "The selected user does not exist."
            ]);

            if (Auth::check()) {
                $base_directory = storage_path("app/public");
                $directory = $base_directory . "/user_" . $validated["id"];

                if (File::exists($directory)) {
                    File::deleteDirectory($directory);
                }
            }
            $user = User::findOrFail($validated["id"]);
            $data = "
                <b>Hi {$user->name},</b><br><br>
                Your account has been removed:<br><br>
                Name: {$user->name}<br>
                Email: {$user->email}<br><br><br>
                Regards,<br><br>" . config("app.name");

            (new SendMailController())->sendMail($user->email, "Delete Account | " . config("app.name"), $data);

            $user->delete();

            DB::commit();

            return response()->json(["success" => true, "msg" => "User deleted successfully."]);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json(["success" => false, "msg" => $errorMsg]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "A required model was not found. Please check your data and try again."]);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "A database error occurred. Please check your query or constraints."]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "An unexpected error occurred. Please try again later."]);
        }
    }

    // Function to export users
    public function exportUsers()
    {
        try {
            if (Auth::user()->is_admin == 1) {
                return Excel::download(new ExportUsers(), "users.xlsx");
            } elseif (Auth::user()->is_admin == 2) {
                return Excel::download(new ExportStudents(), "students.xlsx");
            }
        } catch (LaravelExcelException $e) {
            return response()->json(["success" => false, "msg" => "An error occurred while exporting the Excel file. Please try again later.", "error" => $e->getMessage()]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "An unexpected error occurred. Please try again later."]);
        }
    }

    // Function to display settings dashboard
    public function settingsDashboard()
    {
        try {
            DB::beginTransaction();

            $title = "Settings | " . config("app.name");
            $curr_user = Auth::user();

            return view("admin_lecturer.settings_dashboard", compact("title", "curr_user"));
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "A required model was not found. Please check your data and try again."], 404);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "A database error occurred. Please check your query or constraints."], 500);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "An unexpected error occurred. Please try again later."], 500);
        }
    }

    // Function to update admin lecturer profile
    public function updateAdminLecturerProfile(Request $request)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validate([
                "id" => "required|exists:users,id",
                "name" => "required|string|max:255",
                "address" => "required|string|max:255",
                "phone" => "required|digits_between:10,13"
            ], [
                "id.required" => "User ID is required.",
                "id.exists" => "User not found.",
                "name.required" => "Full name is required.",
                "name.string" => "Full name must be a string.",
                "name.max" => "Full name cannot exceed 255 characters.",
                "address.required" => "Address is required.",
                "address.string" => "Address must be a string.",
                "address.max" => "Address cannot exceed 255 characters.",
                "phone.required" => "Phone number is required.",
                "phone.digits_between" => "Phone number must be between 10 and 13 digits."
            ]);
            $user_id = $validatedData["id"];
            $user_name = $validatedData["name"];
            $user_address = $validatedData["address"];
            $user_phone = $validatedData["phone"];
            $user = User::findOrFail($user_id);
            $user->name = $user_name;
            $user->save();
            $profileDetail = ProfileDetail::where("user_id", $user_id)->first();

            if (!$profileDetail) {
                $profileDetail = new ProfileDetail();
                $profileDetail->user_id = $user_id;
            }

            $profileDetail->address = $user_address;
            $profileDetail->phone = $user_phone;
            $profileDetail->updated_at = now();
            $profileDetail->save();

            DB::commit();

            return response()->json(["success" => true, "msg" => "User profile updated successfully."]);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json(["success" => false, "msg" => "Validation error: " . $errorMsg]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "User or profile not found. Please check your input and try again."]);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "Database error. There was an issue with your request. Please try again."]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "An unexpected error occurred. Please try again later."]);
        }
    }

    // Function to update admin lecturer password
    public function updateAdminLecturerPassword(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                "id" => "required|exists:users,id",
                "currentPassword" => "required|string|max:255",
                "newPassword" => ["required", "string", "min:8", "max:255", "regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/"],
                "confirmPassword" => ["required", "string", "min:8", "max:255", "regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/"]
            ], [
                "id.required" => "User ID is required.",
                "id.exists" => "User not found.",
                "currentPassword.required" => "Current password is required.",
                "currentPassword.string" => "Current password must be a string.",
                "currentPassword.max" => "Current password cannot exceed 255 characters.",
                "newPassword.required" => "New password is required.",
                "newPassword.string" => "New password must be a string.",
                "newPassword.min" => "New password must be at least 8 characters long.",
                "newPassword.max" => "New password cannot exceed 255 characters.",
                "newPassword.regex" => "New password must contain at least one uppercase letter, one lowercase letter, one digit, and one special character.",
                "confirmPassword.required" => "Password confirmation is required.",
                "confirmPassword.string" => "Password confirmation must be a string.",
                "confirmPassword.min" => "Password confirmation must be at least 8 characters long.",
                "confirmPassword.max" => "Password confirmation cannot exceed 255 characters.",
                "confirmPassword.regex" => "Password confirmation must contain at least one uppercase letter, one lowercase letter, one digit, and one special character."
            ]);

            if ($validator->fails()) {
                return response()->json(["success" => false, "errors" => $validator->errors()->all()]);
            }

            $validated = $validator->validated();
            $user_id = $validated["id"];
            $user_currentPassword = $validated["currentPassword"];
            $user_newPassword = $validated["newPassword"];
            $user_confirmPassword = $validated["confirmPassword"];
            $user = User::findOrFail($user_id);

            if (!Hash::check($user_currentPassword, $user->password)) {
                return response()->json(["success" => false, "errors" => ["currentPassword" => "The current password you entered is incorrect."]]);
            }

            if ($user_newPassword != $user_confirmPassword) {
                return response()->json(["success" => false, "errors" => ["confirmPassword" => "The new password and confirmation password do not match."]]);
            }

            $user->password = Hash::make($user_newPassword);
            $user->save();

            DB::commit();

            return response()->json(["success" => true, "msg" => "User password changed successfully."]);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json(["success" => false, "msg" => "Validation error: " . $errorMsg]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "User not found. Please check your input and try again."]);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "Database error. There was an issue with your request. Please try again."]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(["success" => false, "msg" => "An unexpected error occurred. Please try again later."]);
        }
    }
}
