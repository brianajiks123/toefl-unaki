<?php

namespace App\Http\Controllers;

use App\Models\{
    Exam,
    ExamSession,
    ExamTimer,
    FileListening,
    ListeningQuestion,
    ProfileDetail,
    Reading,
    ReadingQuestion,
    SweQuestion,
    User,
    UserAudioPlay,
    UserExamLink,
};
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    DB,
    File,
    Hash,
    Storage,
    Validator,
};
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    // Private Function to ensure directory permissions
    private function ensureDirectoryPermissions($dirPath, $permissions)
    {
        // Create directory if it doesn't exist
        if (!is_dir($dirPath)) {
            mkdir($dirPath, $permissions, true);
        }

        // Set permissions for the directory
        chmod($dirPath, $permissions);

        // Get all subdirectories
        $subDirs = glob($dirPath . "/*", GLOB_ONLYDIR);

        // Recursively set permissions for subdirectories
        foreach ($subDirs as $subDir) {
            $this->ensureDirectoryPermissions($subDir, $permissions);
        }
    }

    // Private Function to calculate total questions in a category
    private function totalQuestionCategory($categoryName)
    {
        // Define total questions per category
        $categories = [
            'Listening' => 50,
            'Structure' => 40,
            'Reading' => 50
        ];

        return $categories[$categoryName] ?? 0;
    }

    // Private Function to convert score based on category and total correct answers
    private function convertScore($category, $totalCorrect)
    {
        // Define score conversion tables for each category
        $conversionValues = [
            'Listening' => [
                0 => 24,
                1 => 25,
                2 => 26,
                3 => 27,
                4 => 28,
                5 => 29,
                6 => 30,
                7 => 31,
                8 => 32,
                9 => 32,
                10 => 33,
                11 => 35,
                12 => 37,
                13 => 38,
                14 => 39,
                15 => 41,
                16 => 41,
                17 => 42,
                18 => 43,
                19 => 44,
                20 => 45,
                21 => 45,
                22 => 46,
                23 => 47,
                24 => 47,
                25 => 48,
                26 => 48,
                27 => 49,
                28 => 49,
                29 => 50,
                30 => 51,
                31 => 51,
                32 => 52,
                33 => 52,
                34 => 53,
                35 => 54,
                36 => 54,
                37 => 55,
                38 => 56,
                39 => 57,
                40 => 57,
                41 => 58,
                42 => 59,
                43 => 60,
                44 => 61,
                45 => 62,
                46 => 63,
                47 => 65,
                48 => 66,
                49 => 67,
                50 => 68
            ],
            'Structure' => [
                0 => 20,
                1 => 20,
                2 => 21,
                3 => 22,
                4 => 23,
                5 => 25,
                6 => 26,
                7 => 27,
                8 => 29,
                9 => 31,
                10 => 33,
                11 => 35,
                12 => 36,
                13 => 37,
                14 => 38,
                15 => 40,
                16 => 40,
                17 => 41,
                18 => 42,
                19 => 43,
                20 => 44,
                21 => 45,
                22 => 46,
                23 => 47,
                24 => 48,
                25 => 49,
                26 => 50,
                27 => 51,
                28 => 52,
                29 => 53,
                30 => 54,
                31 => 55,
                32 => 56,
                33 => 57,
                34 => 58,
                35 => 60,
                36 => 61,
                37 => 63,
                38 => 65,
                39 => 67,
                40 => 68
            ],
            'Reading' => [
                0 => 21,
                1 => 22,
                2 => 23,
                3 => 23,
                4 => 24,
                5 => 25,
                6 => 26,
                7 => 27,
                8 => 28,
                9 => 28,
                10 => 29,
                11 => 30,
                12 => 31,
                13 => 32,
                14 => 34,
                15 => 35,
                16 => 36,
                17 => 37,
                18 => 38,
                19 => 39,
                20 => 40,
                21 => 41,
                22 => 42,
                23 => 43,
                24 => 43,
                25 => 44,
                26 => 45,
                27 => 46,
                28 => 46,
                29 => 47,
                30 => 48,
                31 => 48,
                32 => 49,
                33 => 50,
                34 => 51,
                35 => 52,
                36 => 52,
                37 => 53,
                38 => 54,
                39 => 54,
                40 => 55,
                41 => 56,
                42 => 57,
                43 => 58,
                44 => 59,
                45 => 60,
                46 => 61,
                47 => 63,
                48 => 65,
                49 => 66,
                50 => 67
            ]
        ];

        return $conversionValues[$category][$totalCorrect] ?? 0;
    }

    // Private Function to calculate final score based on total score and category count
    private function finalScore($totalScore, $categoryCount)
    {
        return round($totalScore * 10 / $categoryCount);
    }

    // Function to handle student exams
    public function studentExams()
    {
        try {
            DB::beginTransaction();

            // Set page title
            $title = "Exam Dashboard | " . config("app.name");

            // Get current authenticated user
            $curr_user = Auth::user();

            // Retrieve paginated batches for the user
            $batches = $curr_user->batches()->paginate(1);

            // Map listening file names for each batch
            $file_listening_names = $batches->getCollection()->mapWithKeys(function ($batch) {
                return [
                    $batch->name => DB::table("batch_category_file_listenings")
                        ->join("file_listenings", "batch_category_file_listenings.file_listening_id", "=", "file_listenings.id")
                        ->where("batch_category_file_listenings.batch_id", $batch->id)
                        ->get(["file_listenings.name", "file_listenings.audio_path"])
                        ->toArray()
                ];
            });

            // Process listening files
            foreach ($file_listening_names as $batchName => $fileListenings) {
                $batch_name = str_replace(" ", "_", $batchName);
                $userDir = "public/user_" . Auth::id() . "/" . $batch_name;

                $this->ensureDirectoryPermissions(storage_path("app/" . $userDir), 493);

                foreach ($fileListenings as $fileListening) {
                    $sourcePath = "app/";
                    $audioDir = storage_path($sourcePath . $fileListening->audio_path);
                    $filePathLocal = $userDir . "/" . str_replace(" ", "_", $fileListening->name);

                    if (!Storage::exists($filePathLocal)) {
                        if (File::exists($audioDir)) {
                            Storage::put($filePathLocal, File::get($audioDir));
                        } else {
                            return response()->json(["message" => "File does not exist."], 404);
                        }
                    } else {
                        $size = Storage::size($filePathLocal);
                        $sizeAudios = File::size($audioDir);

                        if ($size !== $sizeAudios) {
                            Storage::delete($filePathLocal);
                            Storage::put($filePathLocal, File::get($audioDir));
                        }
                    }
                }
            }

            // Map reading file names for each batch
            $reading_names = $batches->getCollection()->mapWithKeys(function ($batch) {
                return [
                    $batch->name => DB::table("batch_category_readings")
                        ->join("readings", "batch_category_readings.reading_id", "=", "readings.id")
                        ->where("batch_category_readings.batch_id", $batch->id)
                        ->get(["readings.name", "readings.image_path"])
                        ->toArray()
                ];
            });

            // Process reading files
            foreach ($reading_names as $batchName => $readings) {
                $batch_name = str_replace(" ", "_", $batchName);
                $userDir = "public/user_" . Auth::id() . "/" . $batch_name;

                if (!Storage::exists($userDir)) {
                    Storage::makeDirectory($userDir);
                }

                foreach ($readings as $reading) {
                    $sourcePath = "app/";
                    $imageDir = storage_path($sourcePath . $reading->image_path);
                    $filePathLocal = $userDir . "/" . str_replace(" ", "_", $reading->name);

                    if (!Storage::exists($filePathLocal)) {
                        if (File::exists($imageDir)) {
                            Storage::put($filePathLocal, File::get($imageDir));
                        } else {
                            return response()->json(["message" => "File does not exist."], 404);
                        }
                    } else {
                        $size = Storage::size($filePathLocal);
                        $sizeImages = File::size($imageDir);

                        if ($size !== $sizeImages) {
                            Storage::delete($filePathLocal);
                            Storage::put($filePathLocal, File::get($imageDir));
                        }
                    }
                }
            }

            // Retrieve user audio plays
            $userAudioPlays = UserAudioPlay::where("user_id", $curr_user->id)->get();

            // Map batch exams with links
            $batchExams = $batches->getCollection()->mapWithKeys(function ($batch) use ($curr_user) {
                return [
                    $batch->name => $batch->exams->map(function ($exam) use ($curr_user) {
                        $exam->exam_date = \Carbon\Carbon::parse($exam->exam_date);
                        $exam->exam_time = \Carbon\Carbon::parse($exam->exam_time);
                        $link = DB::table("user_exam_links")
                            ->where("user_id", $curr_user->id)
                            ->where("exam_id", $exam->id)
                            ->value("link");
                        $exam->link = $link;

                        return $exam;
                    })->unique("id")
                ];
            });

            // Collect unique exam links
            $examLinks = $batchExams->collapse()->pluck("link")->filter()->unique()->values()->toArray();

            DB::commit();

            return view("student.exam_dashboard", compact("title", "batches", "batchExams", "examLinks"));
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

    // Function to show exam details
    public function showExam($exam_id, $user_id, $token)
    {
        try {
            DB::beginTransaction();

            $exam = Exam::with(['batches', 'categories'])->where('id', $exam_id)->first();

            if (!$exam) {
                return response()->json(['message' => 'Exam not found'], 404);
            }

            $batch = $exam->batches()->first();

            if (!$batch) {
                DB::rollBack();
                return response()->json(['message' => 'Batch not found'], 404);
            }

            $batchId = $batch->id;
            $batchName = $batch->name;

            $validToken = UserExamLink::where('exam_id', $exam_id)
                ->where('user_id', $user_id)
                ->where('token', $token)
                ->first();

            if (!$validToken) {
                return abort(403, 'Unauthorized access');
            }

            $currentPart = $validToken->current_part;
            $category = $exam->categories()->first();

            if (!$category) {
                DB::rollBack();

                return response()->json(['message' => 'Category not found'], 404);
            }

            $title = "TOEFL - {$category->name} | " . config('app.name');
            $exam_timer = ExamTimer::where('user_id', $user_id)
                ->where('exam_id', $exam_id)
                ->first();

            if ($exam_timer) {
                $exam->exam_time = $exam_timer->remaining_time;
            }

            if ($category->name == 'Listening') {
                $fileListenings = FileListening::whereHas('batches', fn($query) => $query->where('batch_id', $batchId))
                    ->whereHas('categories', fn($query) => $query->where('category_id', $category->id))
                    ->get();

                $userAudioPlays = UserAudioPlay::where('user_id', $user_id)
                    ->get()
                    ->keyBy('file_listening_id');

                foreach ($fileListenings as $fileListening) {
                    if (!isset($userAudioPlays[$fileListening->id])) {
                        UserAudioPlay::create([
                            'user_id' => $user_id,
                            'file_listening_id' => $fileListening->id,
                            'status_played' => 0
                        ]);
                    }
                }

                $userAudioPlays = UserAudioPlay::where('user_id', $user_id)
                    ->get()
                    ->keyBy('file_listening_id');

                $fileListenings->each(function ($fileListening) {
                    $fileListening->questions->each(function ($question) {
                        $options = collect([
                            $question->option_1,
                            $question->option_2,
                            $question->option_3,
                            $question->option_4
                        ])->shuffle();
                        $question->shuffled_options = $options;
                    });
                });

                DB::commit();

                return view('student.exams.listening_exam', compact('title', 'exam', 'fileListenings', 'userAudioPlays', 'exam_timer', 'currentPart', 'batchName'));
            }

            if ($category->name == 'Structure') {
                $structures = SweQuestion::whereHas('batches', fn($query) => $query->where('batch_id', $batchId))
                    ->whereHas('categories', fn($query) => $query->where('category_id', $category->id))
                    ->get();
                $sweQuestions = $structures->shuffle()->shuffle()->shuffle();

                DB::commit();

                return view('student.exams.swe_exam', compact('title', 'exam', 'sweQuestions', 'exam_timer', 'currentPart'));
            }

            if ($category->name == 'Reading') {
                $readings = Reading::whereHas('batches', fn($query) => $query->where('batch_id', $batchId))
                    ->whereHas('categories', fn($query) => $query->where('category_id', $category->id))
                    ->with('questions')
                    ->get()
                    ->shuffle()
                    ->map(function ($reading) {
                        $reading->questions = $reading->questions->shuffle()->map(function ($question) {
                            $options = collect([
                                $question->option_1,
                                $question->option_2,
                                $question->option_3,
                                $question->option_4
                            ])->shuffle();
                            $question->shuffled_options = $options->values();
                            return $question;
                        });
                        return $reading;
                    });

                DB::commit();

                return view('student.exams.reading_exam', compact('title', 'exam', 'readings', 'exam_timer', 'batchName', 'currentPart'));
            }

            DB::rollBack();

            return response()->json(['message' => 'Exam category not found'], 404);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'msg' => 'A required model was not found. Please check your data and try again.'
            ], 404);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'msg' => 'A database error occurred. Please check your query or constraints.'
            ], 500);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'msg' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    // Function to update the status of file listening
    public function updateStatusFileListening(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate request data
            $validated = $request->validate([
                'id_user' => 'required|integer|exists:users,id',
                'id_file_listening' => 'required|integer|exists:file_listenings,id',
                'played_status' => 'required|boolean'
            ], [
                'id_user.required' => 'User ID is required.',
                'id_user.integer' => 'User ID must be an integer.',
                'id_user.exists' => 'The selected user does not exist.',
                'id_file_listening.required' => 'File listening ID is required.',
                'id_file_listening.integer' => 'File listening ID must be an integer.',
                'id_file_listening.exists' => 'The selected file listening does not exist.',
                'played_status.required' => 'Played status is required.',
                'played_status.boolean' => 'Played status must be true or false.'
            ]);

            // Find or create UserAudioPlay record
            $user_audio_play = UserAudioPlay::firstOrNew([
                'user_id' => $validated['id_user'],
                'file_listening_id' => $validated['id_file_listening']
            ]);
            $user_audio_play->status_played = $validated['played_status'];
            $user_audio_play->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'msg' => 'File listening status updated successfully.'
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(', ', $messages);

            return response()->json([
                'success' => false,
                'msg' => $errorMsg
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'msg' => 'A required model was not found. Please check your data and try again.'
            ]);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'msg' => 'A database error occurred. Please check your query or constraints.'
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'msg' => 'An unexpected error occurred. Please try again later.'
            ]);
        }
    }

    // Function to submit exam answers
    public function submitExam(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate request data
            $validatedData = $request->validate([
                'exam_id' => 'required|integer|exists:exams,id'
            ], [
                'exam_id.required' => 'Exam ID is required.',
                'exam_id.integer' => 'Exam ID must be an integer.',
                'exam_id.exists' => 'The selected exam does not exist.'
            ]);

            // Retrieve exam
            $exam_id = $validatedData['exam_id'];
            $exam = Exam::findOrFail($exam_id);
            $exam_categories = $exam->categories;

            // Check if categories exist
            if ($exam_categories->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'msg' => 'Exam category not found.'
                ]);
            }

            // Get first category
            $category = $exam_categories->pluck('name')->first();
            $totalQuestions = $this->totalQuestionCategory($category);
            $answers = $request->except('_token', 'exam_id');
            $correctAnswers = 0;
            $score = 0;

            // Determine question model based on category
            switch ($category) {
                case 'Listening':
                    $questionModel = ListeningQuestion::class;

                    break;
                case 'Structure':
                    $questionModel = SweQuestion::class;

                    break;
                case 'Reading':
                    $questionModel = ReadingQuestion::class;

                    break;
                default:
                    throw new Exception('Invalid category.');
            }

            // Evaluate answers
            foreach ($answers as $key => $answer) {
                $questionId = explode('_', $key)[1] ?? null;
                $question = $questionModel::find($questionId);

                if ($question && $question->ans_correct == $answer) {
                    $correctAnswers++;
                }
            }

            // Calculate score
            $score = $this->convertScore($category, $correctAnswers);

            // Update or create exam session
            if ($score > 0) {
                ExamSession::updateOrCreate(
                    [
                        'user_id' => Auth::id(),
                        'exam_id' => $exam_id
                    ],
                    [
                        'score' => $score
                    ]
                );
            }

            // Calculate unanswered questions
            $unansweredQuestions = $totalQuestions - count($answers);

            DB::commit();

            return response()->json([
                'success' => true,
                'correct_answers' => $correctAnswers,
                'totalQuestions' => $totalQuestions,
                'unanswered_questions' => $unansweredQuestions
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(', ', $messages);

            return response()->json([
                'success' => false,
                'msg' => $errorMsg
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'msg' => 'A required model was not found. Please check your data and try again.'
            ]);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'msg' => 'A database error occurred. Please check your query or constraints.'
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'msg' => 'An unexpected error occurred. Please try again later.'
            ]);
        }
    }

    // Function to update exam session score
    // This function is called when the exam is submitted to update the score in the ExamSession
    public function updateExamSession(Request $request)
    {
        // Validate request data
        $validatedData = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'exam_id' => 'required|integer|exists:exams,id'
        ], [
            'user_id.required' => 'User ID is required.',
            'user_id.integer' => 'User ID must be an integer.',
            'user_id.exists' => 'The selected user does not exist.',
            'exam_id.required' => 'Exam ID is required.',
            'exam_id.integer' => 'Exam ID must be an integer.',
            'exam_id.exists' => 'The selected exam does not exist.'
        ]);

        // Check if the user_id matches the authenticated user
        if ($validatedData['user_id'] == Auth::id()) {
            // Retrieve exam
            $exam = Exam::where('id', $validatedData['exam_id'])->first();
            $exam_explodes = explode('-', $exam->exam_name);
            $exam_category = trim(end($exam_explodes));

            // Assign score based on exam category
            if ($exam_category == 'Listening') {
                $score = 24;
            } elseif ($exam_category == 'Structure') {
                $score = 20;
            } elseif ($exam_category == 'Reading') {
                $score = 21;
            } else {
                $score = 0;
            }

            // Update or create exam session with score
            ExamSession::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'exam_id' => $validatedData['exam_id']
                ],
                [
                    'score' => $score
                ]
            );
        }

        return response()->json([
            'success' => true,
            'msg' => 'Exam session updated successfully.'
        ]);
    }

    // Function to calculate the final score based on total score and category count
    public function studentExamResult()
    {
        try {
            DB::beginTransaction();

            // Set page title
            $title = "Exam Result | " . config("app.name");

            // Get current authenticated user
            $curr_user = Auth::user();

            // Retrieve paginated batches for the user
            $batches = $curr_user->batches()->paginate(1);
            $batchExamResults = [];
            $categoryCount = 0;
            $totalScore = 0;

            // Iterate through batches
            foreach ($batches as $batch) {
                foreach ($batch->categories as $category) {
                    // Get exam for the batch
                    $exam = $category->exams()->where("batch_id", $batch->id)->first();

                    // Initialize batch exam results if not set
                    if ($exam) {
                        // Get exam session for the user
                        $examSession = $exam->examSessions()->where("user_id", $curr_user->id)->first();
                        $score = $examSession ? $examSession->score : 0;
                        $totalScore += $score;
                        $categoryCount++;
                        // Store category results
                        $batchExamResults[$batch->id][] = [
                            "category_name" => $category->name,
                            "score" => $score
                        ];
                    }
                }

                // Calculate final score
                $finalScore = $categoryCount > 0 ? $this->finalScore($totalScore, $categoryCount) : 0;
                $batchExamResults[$batch->id]["final_score"] = $finalScore;
            }

            DB::commit();

            return view("student.exam_result", compact("title", "batches", "batchExamResults"));
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "User not found. Please check your input and try again."
            ], 404);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "Database error. There was an issue with your request. Please try again."
            ], 500);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "msg" => "An unexpected error occurred. Please try again later."
            ], 500);
        }
    }

    // Function to display the student settings dashboard
    public function settingsDashboard()
    {
        try {
            DB::beginTransaction();

            // Set page title
            $title = "Student Settings | " . config("app.name");

            // Get authenticated user ID
            $user_id = Auth::user()->id;

            // Query students with specific conditions
            $students = User::where("users.is_admin", 0)
                ->whereNotNull("users.email_verified_at")
                ->where("users.id", $user_id)
                ->with(["profileDetail" => function ($query) {
                    $query->select("id", "user_id", "address", "phone");
                }])
                ->get();

            DB::commit();

            return view("student.settings_dashboard", compact("title", "students"));
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

    // Function to update student profile
    public function updateStudentProfile(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate request data
            $validatedData = $request->validate([
                'id' => 'required|exists:users,id',
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'phone' => 'required|digits_between:10,13',
            ], [
                'id.required' => 'User ID is required.',
                'id.exists' => 'User not found.',
                'name.required' => 'Full name is required.',
                'name.string' => 'Full name must be a string.',
                'name.max' => 'Full name cannot exceed 255 characters.',
                'address.required' => 'Address is required.',
                'address.string' => 'Address must be a string.',
                'address.max' => 'Address cannot exceed 255 characters.',
                'phone.required' => 'Phone number is required.',
                'phone.digits_between' => 'Phone number must be between 10 and 13 digits.',
            ]);

            // Extract validated data
            $user_id = $validatedData['id'];
            $user_name = $validatedData['name'];
            $user_address = $validatedData['address'];
            $user_phone = $validatedData['phone'];

            // Update user
            $user = User::findOrFail($user_id);
            $user->name = $user_name;
            $user->save();

            // Update or create profile detail
            $profileDetail = ProfileDetail::where('user_id', $user_id)->first();

            if (!$profileDetail) {
                $profileDetail = new ProfileDetail();
                $profileDetail->user_id = $user_id;
            }

            $profileDetail->address = $user_address;
            $profileDetail->phone = $user_phone;
            $profileDetail->updated_at = now();
            $profileDetail->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'msg' => 'User profile updated successfully.'
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(', ', $messages);

            return response()->json([
                'success' => false,
                'msg' => 'Validation error: ' . $errorMsg
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'msg' => 'User or profile not found. Please check your input and try again.'
            ]);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'msg' => 'Database error. There was an issue with your request. Please try again.'
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'msg' => 'An unexpected error occurred. Please try again later.'
            ]);
        }
    }

    // Function to update student password
    public function updateStudentPassword(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate request data
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:users,id',
                'currentPassword' => 'required|string|max:255',
                'newPassword' => [
                    'required',
                    'string',
                    'min:8',
                    'max:255',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
                ],
                'confirmPassword' => [
                    'required',
                    'string',
                    'min:8',
                    'max:255',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
                ],
            ], [
                'id.required' => 'User ID is required.',
                'id.exists' => 'User not found.',
                'currentPassword.required' => 'Current password is required.',
                'currentPassword.string' => 'Current password must be a string.',
                'currentPassword.max' => 'Current password cannot exceed 255 characters.',
                'newPassword.required' => 'New password is required.',
                'newPassword.string' => 'New password must be a string.',
                'newPassword.min' => 'New password must be at least 8 characters long.',
                'newPassword.max' => 'New password cannot exceed 255 characters.',
                'newPassword.regex' => 'New password must contain at least one uppercase letter, one lowercase letter, one digit, and one special character.',
                'confirmPassword.required' => 'Password confirmation is required.',
                'confirmPassword.string' => 'Password confirmation must be a string.',
                'confirmPassword.min' => 'Password confirmation must be at least 8 characters long.',
                'confirmPassword.max' => 'Password confirmation cannot exceed 255 characters.',
                'confirmPassword.regex' => 'Password confirmation must contain at least one uppercase letter, one lowercase letter, one digit, and one special character.',
            ]);

            // Check for validation errors
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()->all()
                ]);
            }

            // Extract validated data
            $validated = $validator->validated();
            $user_id = $validated['id'];
            $user_currentPassword = $validated['currentPassword'];
            $user_newPassword = $validated['newPassword'];
            $user_confirmPassword = $validated['confirmPassword'];

            // Find user
            $user = User::findOrFail($user_id);

            // Verify current password
            if (!Hash::check($user_currentPassword, $user->password)) {
                return response()->json([
                    'success' => false,
                    'errors' => ['currentPassword' => 'The current password you entered is incorrect.']
                ]);
            }

            // Check if new password matches confirmation
            if ($user_newPassword != $user_confirmPassword) {
                return response()->json([
                    'success' => false,
                    'errors' => ['confirmPassword' => 'The new password and confirmation password do not match.']
                ]);
            }

            // Update password
            $user->password = Hash::make($user_newPassword);
            $user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'msg' => 'User password changed successfully.'
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(', ', $messages);

            return response()->json([
                'success' => false,
                'msg' => 'Validation error: ' . $errorMsg
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'msg' => 'User not found. Please check your input and try again.'
            ]);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'msg' => 'Database error. There was an issue with your request. Please try again.'
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'msg' => 'An unexpected error occurred. Please try again later.'
            ]);
        }
    }
}
