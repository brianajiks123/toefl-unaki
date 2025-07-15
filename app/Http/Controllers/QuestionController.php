<?php

namespace App\Http\Controllers;

use App\Imports\{
    ListenQuestImport,
    ReadingQuestImport,
    SingleListenQuestImport,
    SingleReadingQuestImport,
    SingleSweQuestImport,
};
use App\Models\{
    Batch,
    FileListening,
    ListeningQuestion,
    Reading,
    ReadingQuestion,
    SweQuestion,
};
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{
    Auth,
    DB,
    File,
    Log,
    Storage,
    Validator,
};
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Exceptions\LaravelExcelException;
use Maatwebsite\Excel\Facades\Excel;

class QuestionController extends Controller
{
    // Function to ensure directory permissions
    private function ensureDirectoryPermissions($dirPath, $permissions)
    {
        if (!is_dir($dirPath)) {
            mkdir($dirPath, $permissions, true);
        }

        chmod($dirPath, $permissions);

        $subDirs = glob($dirPath . "/*", GLOB_ONLYDIR);

        foreach ($subDirs as $subDir) {
            $this->ensureDirectoryPermissions($subDir, $permissions);
        }
    }

    // Function to handle admin lecturer listen questions
    public function adminLecturerListenQuestions()
    {
        try {
            $title = "Listening Comprehension | " . config('app.name');
            $batches = Batch::whereHas('fileListenings', function ($query) {
                $query->select(DB::raw('batch_category_file_listenings.batch_id'))
                    ->groupBy('batch_category_file_listenings.batch_id')
                    ->havingRaw('COUNT(DISTINCT file_listenings.part) < 3');
            })->orWhereDoesntHave('fileListenings')->get();
            $batches_file_listenings = Batch::with('fileListenings.questions')->paginate(1);

            foreach ($batches_file_listenings as $batch) {
                $batchName = str_replace(" ", "_", $batch->name);
                $userDir = "public/user_" . Auth::id() . "/" . $batchName;

                $this->ensureDirectoryPermissions(storage_path("app/" . $userDir), 493);

                foreach ($batch->fileListenings as $fileListening) {
                    $sourcePath = "app/";
                    $audioDir = storage_path($sourcePath . $fileListening->audio_path);
                    $filePathLocal = $userDir . "/" . str_replace(" ", "_", $fileListening->name);

                    if (!Storage::exists($filePathLocal)) {
                        if (File::exists($audioDir)) {
                            Storage::put($filePathLocal, File::get($audioDir));
                        } else {
                            return response()->json(['message' => 'File does not exist.'], 404);
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

            return view('admin_lecturer.questions.listening_compre', compact('title', 'batches', 'batches_file_listenings'));
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'A required model was not found. Please check your data and try again.'], 404);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'A database error occurred. Please check your query or constraints.'], 500);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'An unexpected error occurred. Please try again later.'], 500);
        }
    }

    // Function to add listening question
    public function addListeningQuest(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'batch_id' => 'required|integer|exists:batches,id',
                'audio_files.*' => 'required|file|mimes:mp3',
                'listening_quest_file' => 'required|file|mimes:xlsx|max:20480'
            ], [
                'batch_id.required' => 'Batch ID is required.',
                'batch_id.integer' => 'Batch ID must be an integer.',
                'batch_id.exists' => 'The selected batch does not exist.',
                'audio_files.*.required' => 'Audio file is required.',
                'audio_files.*.file' => 'Audio files must be valid files.',
                'audio_files.*.mimes' => 'Audio files must be of type mp3.',
                'listening_quest_file.required' => 'Listening question file is required.',
                'listening_quest_file.file' => 'Listening question file must be a valid file.',
                'listening_quest_file.mimes' => 'Listening question file must be of type xlsx.',
                'listening_quest_file.max' => 'Listening question file size may not exceed 10 MB.'
            ]);
            $validParts = ['A', 'B', 'C'];
            $audio_conds = [];
            $audio_files = $validated['audio_files'];
            $excel_file = $validated['listening_quest_file'];

            if ($audio_files) {
                Log::info('File upload attempt (audios):', ['file' => $audio_files]);

                $key = 1;

                foreach ($audio_files as $audio_file) {
                    Log::info("Audio file details {$key} :", [
                        'path' => $audio_file->getPathname(),
                        'size' => $audio_file->getSize(),
                        'mime' => $audio_file->getMimeType(),
                        'readable' => is_readable($audio_file->getPathname()),
                    ]);

                    $key++;
                }
            }

            if ($excel_file) {
                Log::info('File upload attempt (excel: listening):', ['file' => $excel_file]);
                Log::info('Excel file details:', [
                    'path' => $excel_file->getPathname(),
                    'size' => $excel_file->getSize(),
                    'mime' => $excel_file->getMimeType(),
                    'readable' => is_readable($excel_file->getPathname()),
                ]);
            }

            if ($audio_files && $excel_file) {
                $audio_files = $request->file('audio_files');
                $category_id = 1;
                $batch = Batch::findOrFail($validated['batch_id']);
                $batch_id = $batch->id;
                $batch_name = $batch->name;
                $batch_folder = str_replace(" ", "_", $batch_name);

                foreach ($audio_files as $audio_file) {
                    $fileName = $audio_file->getClientOriginalName();

                    if ($audio_file->isValid()) {
                        $batch_filename = explode("_", $fileName);

                        if (count($batch_filename) == 5 && $batch_filename[0] == 'listening' && $batch_filename[1] == 'toefl' && ucwords($batch_filename[2] . "_" . $batch_filename[3]) == $batch_folder) {
                            $part = explode(".", explode("_", $fileName)[4])[0] ?? 'Unknown';

                            if (!in_array($part, $validParts)) {
                                return response()->json(['success' => false, 'msg' => "Invalid part value in filename: {$part}"]);
                            }

                            $audio_conds[] = true;
                        } else {
                            return response()->json(['success' => false, 'msg' => "Audio filename does not match the expected format: " . $fileName]);
                        }
                    } else {
                        return response()->json(['success' => false, 'msg' => "Uploaded file is not valid: " . $fileName]);
                    }
                }

                $listening_quest_file = $request->file('listening_quest_file');

                if ($listening_quest_file->isValid()) {
                    $filename = $listening_quest_file->getClientOriginalName();
                    $batch_filename = explode("_", $filename);
                    $filename_without_ext = explode(".", $batch_filename[3])[0];

                    if (!(count($batch_filename) == 4 && $batch_filename[0] == 'listening' && $batch_filename[1] == 'options' && ucwords($batch_filename[2] . "_" . $filename_without_ext) == $batch_folder)) {
                        return response()->json(['success' => false, 'msg' => "Excel filename does not match the expected format: " . $filename]);
                    }
                } else {
                    return response()->json(['success' => false, 'msg' => 'Uploaded file is not valid.']);
                }

                $fileListenings = [];

                if (!in_array(false, $audio_conds, true)) {
                    foreach ($audio_files as $audio_file) {
                        if (!$audio_file->isValid()) {
                            continue;
                        }

                        $fileName = $audio_file->getClientOriginalName();
                        $part = explode(".", explode("_", $fileName)[4])[0] ?? 'Unknown';
                        $size = $audio_file->getSize();
                        $batch_fileName = explode("_", $fileName);
                        $batchNameFile = ucwords($batch_fileName[2] . "_" . $batch_fileName[3]);
                        $storagePath = "audios/{$batchNameFile}";

                        if (!Storage::disk('local')->exists($storagePath)) {
                            Storage::disk('local')->makeDirectory($storagePath);
                        }

                        $audio_file->storeAs($storagePath, $fileName, 'local');
                        $filePath = $storagePath . "/" . $fileName;
                        $fileListening = FileListening::create([
                            'name' => $fileName,
                            'audio_path' => $filePath,
                            'size' => $size,
                            'part' => $part
                        ]);
                        $fileListening->batches()->attach($batch_id, ['category_id' => $category_id]);
                        $fileListenings[$part] = $fileListening;
                    }
                } else {
                    return response()->json(['success' => false, 'msg' => 'Audio format is not valid!']);
                }

                try {
                    $import = new ListenQuestImport();

                    Excel::import($import, $listening_quest_file);
                } catch (LaravelExcelException $e) {
                    return response()->json(['success' => false, 'msg' => 'Excel package error: ' . $e->getMessage()]);
                } catch (ValidationException $e) {
                    return response()->json(['success' => false, 'msg' => 'Validation error: ' . $e->getMessage()]);
                } catch (Exception $e) {
                    return response()->json(['success' => false, 'msg' => 'Failed to import Excel file: ' . $e->getMessage()]);
                }

                foreach ($validParts as $part) {
                    if (isset($fileListenings[$part])) {
                        $fileListening = $fileListenings[$part];

                        foreach ($import->data['part_' . $part] as $row) {
                            $question = ListeningQuestion::create([
                                'option_1' => $row[0],
                                'option_2' => $row[1],
                                'option_3' => $row[2],
                                'option_4' => $row[3],
                                'ans_correct' => $row[4]
                            ]);
                            $fileListening->questions()->attach($question->id);
                        }
                    }
                }

                DB::commit();

                return response()->json(['success' => true, 'msg' => 'Listening Question added successfully.']);
            } else {
                return response()->json(['success' => false, 'msg' => 'No audio or Excel files were uploaded.']);
            }
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json(['success' => false, 'msg' => $errorMsg]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'A required model was not found. Please check your data and try again.']);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'A database error occurred. Please check your query or constraints.']);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'An unexpected error occurred. Please try again later.']);
        }
    }

    // Function to update file listening
    public function updateFileListening(Request $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validate([
                'edit_batch_name' => 'required|string|exists:batches,name',
                'edit_audio_file_id' => 'required|integer|exists:file_listenings,id',
                'edit_audio_file_listening' => 'required|file|mimes:mp3'
            ], [
                'edit_batch_name.required' => 'Batch name is required.',
                'edit_batch_name.string' => 'Batch name must be a string.',
                'edit_batch_name.exists' => 'The selected batch does not exist.',
                'edit_audio_file_id.required' => 'Audio file ID is required.',
                'edit_audio_file_id.integer' => 'Audio file ID must be an integer.',
                'edit_audio_file_id.exists' => 'The selected audio file does not exist.',
                'edit_audio_file_listening.required' => 'Listening question file is required.',
                'edit_audio_file_listening.file' => 'Listening question file must be a valid file.',
                'edit_audio_file_listening.mimes' => 'Listening question file must be of type mp3.'
            ]);

            if (!$request->hasFile('edit_audio_file_listening')) {
                return response()->json(['success' => false, 'msg' => 'No audio file uploaded']);
            }

            $audioFile = $validated['edit_audio_file_listening'];
            $validParts = ['A', 'B', 'C'];
            $listening_quest_file = $request->file('edit_audio_file_listening');
            $fileName = $listening_quest_file->getClientOriginalName();

            if ($listening_quest_file->isValid()) {
                $batch_filename = explode("_", $fileName);

                if (count($batch_filename) == 5 && $batch_filename[0] == 'listening' && $batch_filename[1] == 'toefl') {
                    $part = explode(".", explode("_", $fileName)[4])[0] ?? 'Unknown';

                    if (!in_array($part, $validParts)) {
                        return response()->json(['success' => false, 'msg' => "Invalid part value in filename: {$part}"]);
                    }
                } else {
                    return response()->json(['success' => false, 'msg' => "Audio filename does not match the expected format: " . $fileName]);
                }
            } else {
                return response()->json(['success' => false, 'msg' => "Uploaded file is not valid: " . $fileName]);
            }

            $newFileName = $audioFile->getClientOriginalName();
            $new_size = $audioFile->getSize();
            $extractedFileName = explode("_", $newFileName);
            $newBatchName = str_replace(" ", "_", $validated['edit_batch_name']);
            $userBatchFolder = "user_" . Auth::id() . "/" . $newBatchName;
            $newBatchNameFile = ucwords($extractedFileName[2] . "_" . $extractedFileName[3]);
            $newPartFile = explode(".", $extractedFileName[4])[0];
            $oldFileId = $validated['edit_audio_file_id'];
            $oldFileListening = FileListening::findOrFail($oldFileId);
            $oldPartFileName = $oldFileListening->part;
            $pathOldFileAudio = $oldFileListening->audio_path;

            if ($newBatchNameFile == explode("/", $userBatchFolder)[1] && $newPartFile == $oldPartFileName) {
                try {
                    if (Storage::disk('local')->exists($pathOldFileAudio)) {
                        Storage::disk('local')->delete($pathOldFileAudio);
                    }
                } catch (Exception $e) {
                    return response()->json(['success' => false, 'msg' => "Failed to delete old file: " . $e->getMessage()]);
                }

                try {
                    $filePath = $audioFile->storeAs("audios/{$newBatchNameFile}", $newFileName);
                    $oldFileListening->name = $newFileName;
                    $oldFileListening->audio_path = $filePath;
                    $oldFileListening->size = $new_size;
                    $oldFileListening->update();
                } catch (Exception $e) {
                    return response()->json(['success' => false, 'msg' => "Failed to update new file: " . $e->getMessage()]);
                }

                DB::commit();

                return response()->json(['success' => true, 'msg' => "Success update audio file: {$newFileName}"]);
            } else {
                return response()->json(['success' => false, 'msg' => "Filename does not match the expected format: " . $newFileName]);
            }
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json(['success' => false, 'msg' => $errorMsg]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'The specified audio file or question model was not found.']);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'A database error occurred. Please try again later.']);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'An unexpected error occurred. Please try again later.']);
        }
    }

    // Function to add listen option answer manually
    public function addListenOptionAnswerManual(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'audio_file_id_manual' => 'required|integer|exists:file_listenings,id',
                'option_ans_1' => 'required|string',
                'option_ans_2' => 'required|string',
                'option_ans_3' => 'required|string',
                'option_ans_4' => 'required|string',
                'ans_correct' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($request) {
                        $validOptions = [
                            $request->input('option_ans_1'),
                            $request->input('option_ans_2'),
                            $request->input('option_ans_3'),
                            $request->input('option_ans_4')
                        ];

                        if (!in_array($value, $validOptions)) {
                            $fail('The correct answer must be one of the provided options.');
                        }
                    }
                ]
            ], [
                'audio_file_id_manual.required' => 'Audio file ID is required.',
                'audio_file_id_manual.integer' => 'Audio file ID must be an integer.',
                'audio_file_id_manual.exists' => 'The selected audio file does not exist.',
                'option_ans_1.required' => 'Option 1 is required.',
                'option_ans_1.string' => 'Option 1 must be a string.',
                'option_ans_2.required' => 'Option 2 is required.',
                'option_ans_2.string' => 'Option 2 must be a string.',
                'option_ans_3.required' => 'Option 3 is required.',
                'option_ans_3.string' => 'Option 3 must be a string.',
                'option_ans_4.required' => 'Option 4 is required.',
                'option_ans_4.string' => 'Option 4 must be a string.',
                'ans_correct.required' => 'The correct answer is required.',
                'ans_correct.string' => 'The correct answer must be a string.'
            ]);
            $question = ListeningQuestion::create([
                'option_1' => $validated['option_ans_1'],
                'option_2' => $validated['option_ans_2'],
                'option_3' => $validated['option_ans_3'],
                'option_4' => $validated['option_ans_4'],
                'ans_correct' => $validated['ans_correct']
            ]);

            FileListening::findOrFail($validated['audio_file_id_manual'])->questions()->attach($question->id);

            DB::commit();

            return response()->json(['success' => true, 'msg' => 'Option answer added successfully.']);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json(['success' => false, 'msg' => $errorMsg]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'The specified audio file or question model was not found.']);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'A database error occurred. Please try again later.']);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'An unexpected error occurred. Please try again later.']);
        }
    }

    // Function to add listen option answer file
    public function addListenOptionAnswerFile(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'audio_file_id_file' => 'required|integer|exists:file_listenings,id',
                'audio_file_id_batch' => 'required|integer|exists:batches,id',
                'audio_file_id_part' => 'required|string|exists:file_listenings,part',
                'option_ans_file' => 'required|file|mimes:xlsx|max:2048'
            ], [
                'audio_file_id_file.required' => 'Audio file ID is required.',
                'audio_file_id_file.integer' => 'Audio file ID must be an integer.',
                'audio_file_id_file.exists' => 'The selected audio file does not exist.',
                'audio_file_id_batch.required' => 'Batch ID is required.',
                'audio_file_id_batch.integer' => 'Batch ID must be an integer.',
                'audio_file_id_batch.exists' => 'The selected batch does not exist.',
                'audio_file_id_part.required' => 'Part is required.',
                'audio_file_id_part.string' => 'Part must be a string.',
                'audio_file_id_part.exists' => 'The selected part does not exist.',
                'option_ans_file.required' => 'Option answer file is required.',
                'option_ans_file.file' => 'Option answer file must be a valid file.',
                'option_ans_file.mimes' => 'Option MDP answer file must be of type xlsx.',
                'option_ans_file.max' => 'Option answer file may not be greater than 10MB.'
            ]);

            $validParts = ['A', 'B', 'C'];
            $batch_id = $validated['audio_file_id_batch'];
            $batch = Batch::select('name')->where('id', $batch_id)->first();

            if ($batch) {
                $batch_name = $batch->name;
            }

            $userBatchFolder = "user_" . Auth::id() . "/" . str_replace(" ", "_", $batch_name);
            $excel_file = $validated['option_ans_file'];

            if ($excel_file) {
                Log::info('File upload attempt (excel: listening):', ['file' => $excel_file]);
                Log::info('Excel file details:', [
                    'path' => $excel_file->getPathname(),
                    'size' => $excel_file->getSize(),
                    'mime' => $excel_file->getMimeType(),
                    'readable' => is_readable($excel_file->getPathname()),
                ]);
            }

            if ($excel_file && $excel_file->isValid()) {
                $filename = $excel_file->getClientOriginalName();
                $batch_filename = explode("_", $filename);
                $part = explode(".", $batch_filename[4])[0];
                $expected_file_format = "listening_options_batch_x_partX";

                if (count($batch_filename) == 5 && $batch_filename[0] == 'listening' && $batch_filename[1] == 'options' && ucwords($batch_filename[2] . "_" . $batch_filename[3]) == explode("/", $userBatchFolder)[1]) {
                    if (!in_array($part, $validParts)) {
                        DB::rollBack();

                        return response()->json(['success' => false, 'msg' => "Invalid part value in filename: {$part}"]);
                    }

                    if ($part == $validated['audio_file_id_part']) {
                        try {
                            $import = new SingleListenQuestImport($validated['audio_file_id_file']);

                            Excel::import($import, $excel_file);
                        } catch (LaravelExcelException $e) {
                            DB::rollBack();

                            return response()->json(['success' => false, 'msg' => "Excel package error: " . $e->getMessage()]);
                        } catch (ValidationException $e) {
                            DB::rollBack();

                            return response()->json(['success' => false, 'msg' => "Validation error: " . $e->getMessage()]);
                        } catch (Exception $e) {
                            DB::rollBack();

                            return response()->json(['success' => false, 'msg' => "Failed to import Excel file: " . $e->getMessage()]);
                        }

                        DB::commit();

                        return response()->json(['success' => true, 'msg' => 'Option answer imported successfully.']);
                    } else {
                        DB::rollBack();

                        return response()->json(['success' => false, 'msg' => "Excel filename does not match with the part: " . $expected_file_format]);
                    }
                } else {
                    DB::rollBack();

                    return response()->json(['success' => false, 'msg' => "Excel filename does not match the expected format: " . $expected_file_format]);
                }
            } else {
                DB::rollBack();

                return response()->json(['success' => false, 'msg' => 'Uploaded file is not valid.']);
            }
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json(['success' => false, 'msg' => $errorMsg]);
        } catch (PostTooLargeException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'Uploaded file is too large. Please upload a file smaller than 10MB.']);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => "An unexpected error occurred: " . $e->getMessage()]);
        }
    }

    // Function to edit listen option answer
    public function editListenOptionAnswer(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|exists:listening_questions,id',
                'option_ans_1' => 'required|string',
                'option_ans_2' => 'required|string',
                'option_ans_3' => 'required|string',
                'option_ans_4' => 'required|string',
                'ans_correct' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($request) {
                        $validOptions = [
                            $request->input('option_ans_1'),
                            $request->input('option_ans_2'),
                            $request->input('option_ans_3'),
                            $request->input('option_ans_4')
                        ];

                        if (!in_array($value, $validOptions)) {
                            $fail('The correct answer must be one of the provided options.');
                        }
                    }
                ]
            ], [
                'id.required' => 'The ID is required.',
                'id.integer' => 'The ID must be an integer.',
                'id.exists' => 'The selected ID does not exist in the listening questions.',
                'option_ans_1.required' => 'Option 1 is required.',
                'option_ans_1.string' => 'Option 1 must be a string.',
                'option_ans_2.required' => 'Option 2 is required.',
                'option_ans_2.string' => 'Option 2 must be a string.',
                'option_ans_3.required' => 'Option 3 is required.',
                'option_ans_3.string' => 'Option 3 must be a string.',
                'option_ans_4.required' => 'Option 4 is required.',
                'option_ans_4.string' => 'Option 4 must be a string.',
                'ans_correct.required' => 'The correct answer is required.',
                'ans_correct.string' => 'The correct answer must be a string.'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()]);
            }

            $validated = $validator->validated();
            $listening_question = ListeningQuestion::findOrFail($validated['id']);
            $listening_question->update([
                'option_1' => $validated['option_ans_1'],
                'option_2' => $validated['option_ans_2'],
                'option_3' => $validated['option_ans_3'],
                'option_4' => $validated['option_ans_4'],
                'ans_correct' => $validated['ans_correct']
            ]);

            DB::commit();

            return response()->json(['success' => true, 'msg' => 'Option answer updated successfully.']);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json(['success' => false, 'msg' => $errorMsg]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'errors' => ['general' => 'The specified resource was not found.']]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'errors' => ['general' => 'An unexpected error occurred. Please try again later.'], 'exception' => $e->getMessage()]);
        }
    }

    // Function to delete listen option answer
    public function deleteListenOptionAnswer(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'id' => 'required|integer|exists:listening_questions,id'
            ], [
                'id.required' => 'ID is required.',
                'id.integer' => 'ID must be an integer.',
                'id.exists' => 'The selected ID does not exist.'
            ]);
            $listening_question = ListeningQuestion::findOrFail($validated['id']);
            $listening_question->delete();

            DB::commit();

            return response()->json(['success' => true, 'msg' => 'Option answer deleted successfully.']);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json(['success' => false, 'msg' => $errorMsg]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'Resource not found.']);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'An unexpected error occurred: ' . $e->getMessage()]);
        }
    }

    // Function to display SWE questions for admin
    public function adminLecturerSWEQuestions()
    {
        try {
            DB::beginTransaction();

            $title = "Structure & Written Expression | " . config('app.name');
            $batches = Batch::select('id', 'name')->get();
            $allBatches = Batch::with('sweQuestions')->get();
            $groupedByBatch = $allBatches->mapWithKeys(function ($batch) {
                return [$batch->name => $batch->sweQuestions->map(function ($sweQuestion) use ($batch) {
                    return [
                        'id' => $sweQuestion->id,
                        'question' => $sweQuestion->question,
                        'option_1' => $sweQuestion->option_1,
                        'option_2' => $sweQuestion->option_2,
                        'option_3' => $sweQuestion->option_3,
                        'option_4' => $sweQuestion->option_4,
                        'ans_correct' => $sweQuestion->ans_correct,
                        'batch_name' => $batch->name
                    ];
                })];
            });
            $groupedByBatch = $groupedByBatch->map(function ($questions, $batchName) {
                return $questions->isEmpty() ? collect([]) : $questions;
            });
            $perPage = 1;
            $page = LengthAwarePaginator::resolveCurrentPage();
            $total = $groupedByBatch->count();
            $results = $groupedByBatch->slice(($page - 1) * $perPage, $perPage);
            $sweQuestions = new LengthAwarePaginator($results, $total, $perPage, $page, [
                'path' => LengthAwarePaginator::resolveCurrentPath()
            ]);

            DB::commit();

            return view('admin_lecturer.questions.swe_quest', compact('title', 'batches', 'sweQuestions'));
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'The specified question model was not found.'], 404);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'A database error occurred. Please try again later.'], 500);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'An unexpected error occurred. Please try again later.'], 500);
        }
    }

    // Function to add SWE question manually
    public function addSweQuestionManual(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'batch_id' => 'required|integer|exists:batches,id',
                'swe_quest_manual' => 'required|string',
                'option_ans_1' => 'required|string',
                'option_ans_2' => 'required|string',
                'option_ans_3' => 'required|string',
                'option_ans_4' => 'required|string',
                'ans_correct' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($request) {
                        $validOptions = [
                            $request->input('option_ans_1'),
                            $request->input('option_ans_2'),
                            $request->input('option_ans_3'),
                            $request->input('option_ans_4')
                        ];

                        if (!in_array($value, $validOptions)) {
                            $fail('The correct answer must be one of the provided options.');
                        }
                    }
                ]
            ], [
                'batch_id.required' => 'Batch ID is required.',
                'batch_id.integer' => 'Batch ID must be an integer.',
                'batch_id.exists' => 'The selected batch does not exist.',
                'swe_quest_manual.required' => 'Manual question is required.',
                'swe_quest_manual.string' => 'Manual question must be a string.',
                'option_ans_1.required' => 'Option 1 is required.',
                'option_ans_1.string' => 'Option 1 must be a string.',
                'option_ans_2.required' => 'Option 2 is required.',
                'option_ans_2.string' => 'Option 2 must be a string.',
                'option_ans_3.required' => 'Option 3 is required.',
                'option_ans_3.string' => 'Option 3 must be a string.',
                'option_ans_4.required' => 'Option 4 is required.',
                'option_ans_4.string' => 'Option 4 must be a string.',
                'ans_correct.required' => 'The correct answer is required.',
                'ans_correct.string' => 'The correct answer must be a string.'
            ]);
            $category_id = 2;
            $swe_question = SweQuestion::create([
                'question' => $validated['swe_quest_manual'],
                'option_1' => $validated['option_ans_1'],
                'option_2' => $validated['option_ans_2'],
                'option_3' => $validated['option_ans_3'],
                'option_4' => $validated['option_ans_4'],
                'ans_correct' => $validated['ans_correct']
            ]);
            $swe_question->batches()->attach($validated['batch_id'], ['category_id' => $category_id]);

            DB::commit();

            return response()->json(['success' => true, 'msg' => 'Question added successfully.']);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json(['success' => false, 'msg' => $errorMsg]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'The specified question model was not found.']);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'A database error occurred. Please try again later.']);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'An unexpected error occurred. Please try again later.']);
        }
    }

    // Function to add SWE question file
    public function addSweQuestionFile(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'batch_id' => 'required|integer|exists:batches,id',
                'swe_quest_file' => 'required|file|mimes:xlsx,xls|max:2048'
            ], [
                'batch_id.required' => 'Batch ID is required.',
                'batch_id.integer' => 'Batch ID must be an integer.',
                'batch_id.exists' => 'The selected batch does not exist.',
                'swe_quest_file.required' => 'The file is required.',
                'swe_quest_file.file' => 'The file must be a valid file.',
                'swe_quest_file.mimes' => 'The file must be of type xlsx.',
                'swe_quest_file.max' => 'The file size must not exceed 10 MB.'
            ]);
            $swe_file = $validated['swe_quest_file'];
            $category_id = 2;

            if ($swe_file) {
                Log::info('File upload attempt (excel: swe):', ['file' => $swe_file]);
                Log::info('File details', [
                    'path' => $swe_file->getPathname(),
                    'size' => $swe_file->getSize(),
                    'mime' => $swe_file->getMimeType(),
                    'readable' => is_readable($swe_file->getPathname()),
                ]);

                $swe_quest_file = $request->file('swe_quest_file');
                $batch = Batch::findOrFail($validated['batch_id']);
                $batch_name = $batch->name;
                $userBatchFolder = "user_" . Auth::id() . "/" . str_replace(" ", "_", $batch_name);

                if ($swe_quest_file->isValid()) {
                    $filename = $swe_quest_file->getClientOriginalName();
                    $batch_filename = explode("_", $filename);
                    $filename_without_ext = explode(".", $batch_filename[2])[0];

                    if (count($batch_filename) == 3 && $batch_filename[0] == 'swe' && ucwords($batch_filename[1] . "_" . $filename_without_ext) == explode("/", $userBatchFolder)[1]) {
                        try {
                            $import = new SingleSweQuestImport($validated['batch_id'], $category_id);

                            Excel::import($import, $swe_file);
                        } catch (LaravelExcelException $e) {
                            return response()->json(['success' => false, 'msg' => 'Excel package error: ' . $e->getMessage()]);
                        } catch (ValidationException $e) {
                            return response()->json(['success' => false, 'msg' => 'Validation error: ' . $e->getMessage()]);
                        } catch (Exception $e) {
                            return response()->json(['success' => false, 'msg' => 'Failed to import Excel file: ' . $e->getMessage()]);
                        }

                        DB::commit();

                        return response()->json(['success' => true, 'msg' => 'Question imported successfully.']);
                    } else {
                        return response()->json(['success' => false, 'msg' => 'Excel filename does not match the expected format: ' . $filename]);
                    }
                } else {
                    return response()->json(['success' => false, 'msg' => 'Uploaded file is not valid.']);
                }
            }
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json(['success' => false, 'msg' => $errorMsg]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'The specified question model was not found.']);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'A database error occurred. Please try again later.']);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'An unexpected error occurred. Please try again later.']);
        }
    }

    // Function to edit SWE question
    public function editSweQuestion(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|exists:swe_questions,id',
                'edit_swe_quest' => 'required|string',
                'edit_option_ans_1' => 'required|string',
                'edit_option_ans_2' => 'required|string',
                'edit_option_ans_3' => 'required|string',
                'edit_option_ans_4' => 'required|string',
                'edit_ans_correct' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($request) {
                        $validOptions = [
                            $request->input('edit_option_ans_1'),
                            $request->input('edit_option_ans_2'),
                            $request->input('edit_option_ans_3'),
                            $request->input('edit_option_ans_4')
                        ];

                        if (!in_array($value, $validOptions)) {
                            $fail('The correct answer must be one of the provided options.');
                        }
                    }
                ]
            ], [
                'id.required' => 'ID is required.',
                'id.integer' => 'ID must be an integer.',
                'id.exists' => 'The selected question does not exist.',
                'edit_swe_quest.required' => 'Question is required.',
                'edit_swe_quest.string' => 'Question must be a string.',
                'edit_option_ans_1.required' => 'Option 1 is required.',
                'edit_option_ans_1.string' => 'Option 1 must be a string.',
                'edit_option_ans_2.required' => 'Option 2 is required.',
                'edit_option_ans_2.string' => 'Option 2 must be a string.',
                'edit_option_ans_3.required' => 'Option 3 is required.',
                'edit_option_ans_3.string' => 'Option 3 must be a string.',
                'edit_option_ans_4.required' => 'Option 4 is required.',
                'edit_option_ans_4.string' => 'Option 4 must be a string.',
                'edit_ans_correct.required' => 'The correct answer is required.',
                'edit_ans_correct.string' => 'The correct answer must be a string.'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $validated = $validator->validated();
            $swe_question = SweQuestion::findOrFail($validated['id']);
            $swe_question->update([
                'question' => $validated['edit_swe_quest'],
                'option_1' => $validated['edit_option_ans_1'],
                'option_2' => $validated['edit_option_ans_2'],
                'option_3' => $validated['edit_option_ans_3'],
                'option_4' => $validated['edit_option_ans_4'],
                'ans_correct' => $validated['edit_ans_correct']
            ]);

            DB::commit();

            return response()->json(['success' => true, 'msg' => 'Question updated successfully.']);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json(['success' => false, 'msg' => $errorMsg]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'The specified question model was not found.']);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'A database error occurred. Please try again later.']);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'An unexpected error occurred. Please try again later.']);
        }
    }

    // Function to delete SWE question
    public function deleteSweQuestion(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|exists:swe_questions,id'
            ], [
                'id.required' => 'ID is required.',
                'id.integer' => 'ID must be an integer.',
                'id.exists' => 'The selected question does not exist.'
            ]);
            $validated = $validator->validated();
            $swe_question = SweQuestion::findOrFail($validated['id']);
            $swe_question->delete();

            DB::commit();

            return response()->json(['success' => true, 'msg' => 'Question deleted successfully.']);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json(['success' => false, 'msg' => $errorMsg]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'The specified question model was not found.']);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'A database error occurred. Please try again later.']);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'An unexpected error occurred. Please try again later.']);
        }
    }

    // Function to read admin lecturer reading questions
    public function adminLecturerReadingQuestions()
    {
        try {
            DB::beginTransaction();

            // Set page title
            $title = "Reading Comprehension | " . config('app.name');

            // Fetch batches with readings having more than one distinct part or no readings
            $batches = Batch::whereHas('readings', function ($query) {
                $query->select(DB::raw('batch_category_readings.batch_id'))
                    ->groupBy('batch_category_readings.batch_id')
                    ->havingRaw('COUNT(DISTINCT readings.part) > 1');
            })->orWhereDoesntHave('readings')->get();

            // Fetch batches with their readings and questions, paginated
            $batches_readings = Batch::with('readings.questions')->paginate(1);

            // Process each batch for file storage
            foreach ($batches_readings as $batch) {
                $batchName = str_replace(' ', '_', $batch->name);
                $userDir = 'public/user_' . Auth::id() . '/' . $batchName;

                // Create user directory if it doesn't exist
                if (!Storage::exists($userDir)) {
                    Storage::makeDirectory($userDir);
                }

                // Process each reading in the batch
                foreach ($batch->readings as $reading) {
                    $sourcePath = 'app/';
                    $imageDir = storage_path($sourcePath . $reading->image_path);
                    $filePathLocal = $userDir . '/' . str_replace(' ', '_', $reading->name);

                    // Copy image file to user directory if it doesn't exist
                    if (!Storage::exists($filePathLocal)) {
                        if (File::exists($imageDir)) {
                            Storage::put($filePathLocal, File::get($imageDir));
                        } else {
                            return response()->json(['message' => 'File does not exist.']);
                        }
                    } else {
                        // Update file if sizes don't match
                        $size = Storage::size($filePathLocal);
                        $sizeImages = File::size($imageDir);

                        if ($size !== $sizeImages) {
                            Storage::delete($filePathLocal);
                            Storage::put($filePathLocal, File::get($imageDir));
                        }
                    }
                }
            }

            DB::commit();

            return view('admin_lecturer.questions.reading_compre', compact('title', 'batches', 'batches_readings'));
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'The specified question model was not found.']);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'A database error occurred. Please try again later.']);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'An unexpected error occurred. Please try again later.']);
        }
    }

    // Function to add reading question
    public function addReadingQuestion(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate request data
            $validated = $request->validate([
                'batch_id' => 'required|integer|exists:batches,id',
                'reading_files.*' => 'required|file|mimes:jpg,png',
                'reading_quest_file' => 'required|file|mimes:xlsx|max:2048'
            ], [
                'batch_id.required' => 'Batch ID is required.',
                'batch_id.integer' => 'Batch ID must be an integer.',
                'batch_id.exists' => 'The selected batch does not exist.',
                'reading_files.*.required' => 'Reading files are required.',
                'reading_files.*.file' => 'Each reading file must be a valid file.',
                'reading_files.*.mimes' => 'Each reading file must be of type jpg or png.',
                'reading_quest_file.required' => 'Reading question file is required.',
                'reading_quest_file.file' => 'Reading question file must be a valid file.',
                'reading_quest_file.mimes' => 'Reading question file must be of type xlsx.',
                'reading_quest_file.max' => 'Reading question file size may not be greater than 10MB.'
            ]);

            $image_conds = [];
            $validParts = [];
            $reading_files = $validated['reading_files'];
            $excel_file = $validated['reading_quest_file'];

            if ($reading_files) {
                Log::info('File upload attempt (images):', ['file' => $reading_files]);

                $key = 1;

                foreach ($reading_files as $reading_file) {
                    Log::info("Image file details {$key} :", [
                        'path' => $reading_file->getPathname(),
                        'size' => $reading_file->getSize(),
                        'mime' => $reading_file->getMimeType(),
                        'readable' => is_readable($reading_file->getPathname()),
                    ]);

                    $key++;
                }
            }

            if ($excel_file) {
                Log::info('File upload attempt (excel: reading):', ['file' => $excel_file]);
                Log::info('Excel file details:', [
                    'path' => $excel_file->getPathname(),
                    'size' => $excel_file->getSize(),
                    'mime' => $excel_file->getMimeType(),
                    'readable' => is_readable($excel_file->getPathname()),
                ]);
            }

            // Check for uploaded files
            if ($reading_files && $excel_file) {
                $category_id = 3;
                $batch = Batch::findOrFail($validated['batch_id']);
                $batch_id = $batch->id;
                $batch_name = $batch->name;
                $userBatchFolder = 'user_' . Auth::id() . '/' . str_replace(' ', '_', $batch_name);

                // Create user batch folder if it doesn't exist
                if (!Storage::disk('public')->exists($userBatchFolder)) {
                    Storage::disk('public')->makeDirectory($userBatchFolder);
                }

                // Process each reading file
                foreach ($reading_files as $reading_file) {
                    $fileName = $reading_file->getClientOriginalName();

                    if ($reading_file->isValid()) {
                        $batch_filename = explode('_', $fileName);

                        if (count($batch_filename) == 5 && $batch_filename[0] == 'reading' && $batch_filename[1] == 'toefl' && ucwords($batch_filename[2] . '_' . $batch_filename[3]) == explode('/', $userBatchFolder)[1]) {
                            $part = explode('.', $batch_filename[4])[0];
                            $validParts[] = $part;

                            if (!is_numeric($part)) {
                                return response()->json(['success' => false, 'msg' => "Invalid part value in filename: {$part}"]);
                            }

                            $image_conds[] = true;
                        } else {
                            return response()->json(['success' => false, 'msg' => "Image filename does not match the expected format: " . $fileName]);
                        }
                    } else {
                        return response()->json(['success' => false, 'msg' => "Uploaded file is not valid: " . $fileName]);
                    }
                }

                // Validate question file
                $reading_quest_file = $request->file('reading_quest_file');

                if ($reading_quest_file->isValid()) {
                    $filename = $reading_quest_file->getClientOriginalName();
                    $batch_filename = explode('_', $filename);
                    $filename_without_ext = explode('.', $batch_filename[2])[0];

                    if (!(count($batch_filename) == 3 && $batch_filename[0] == 'reading' && $batch_filename[1] == 'batch' && ucwords($batch_filename[1] . '_' . $filename_without_ext) == explode('/', $userBatchFolder)[1])) {
                        return response()->json(['success' => false, 'msg' => "Excel filename does not match the expected format: " . $filename]);
                    }
                } else {
                    return response()->json(['success' => false, 'msg' => 'Uploaded file is not valid.']);
                }

                $fileReadings = [];

                if (!in_array(false, $image_conds, true)) {
                    foreach ($reading_files as $reading_file) {
                        $fileName = $reading_file->getClientOriginalName();
                        $extracted_fileName = explode('_', $fileName);
                        $batchNameFile = ucwords($extracted_fileName[2] . '_' . $extracted_fileName[3]);
                        $size = $reading_file->getSize();
                        $part = explode('.', $extracted_fileName[4])[0] ?? 'Unknown';
                        $filePath = $reading_file->storeAs("images/{$batchNameFile}", $fileName);
                        $fileReading = Reading::create([
                            'name' => $fileName,
                            'image_path' => $filePath,
                            'size' => $size,
                            'part' => $part
                        ]);
                        $reading_file->storeAs($userBatchFolder, $fileName, 'public');
                        $fileReading->batches()->attach($batch_id, ['category_id' => $category_id]);
                        $fileReadings[$part] = $fileReading;
                    }
                } else {
                    return response()->json(['success' => false, 'msg' => 'Image format is not valid!']);
                }

                // Import Excel data
                try {
                    $excel = Excel::toCollection(new \stdClass(), $reading_quest_file);
                    $maxSheet = count($excel);
                    $import = new ReadingQuestImport($maxSheet);

                    Excel::import($import, $reading_quest_file);
                } catch (LaravelExcelException $e) {
                    return response()->json(['success' => false, 'msg' => 'Excel package error: ' . $e->getMessage()]);
                } catch (ValidationException $e) {
                    return response()->json(['success' => false, 'msg' => 'Validation error: ' . $e->getMessage()]);
                } catch (Exception $e) {
                    return response()->json(['success' => false, 'msg' => 'Failed to import Excel file: ' . $e->getMessage()]);
                }

                // Associate questions with readings
                foreach ($validParts as $part) {
                    if (isset($fileReadings[$part])) {
                        $fileReading = $fileReadings[$part];

                        foreach ($import->data['quest_' . $part] as $row) {
                            $question = ReadingQuestion::create([
                                'question' => $row[0],
                                'option_1' => $row[1],
                                'option_2' => $row[2],
                                'option_3' => $row[3],
                                'option_4' => $row[4],
                                'ans_correct' => $row[5]
                            ]);
                            $fileReading->questions()->attach($question->id);
                        }
                    }
                }

                DB::commit();

                return response()->json(['success' => true, 'msg' => 'Reading Question added successfully.']);
            } else {
                return response()->json(['success' => false, 'msg' => 'No jpg/png or Excel files were uploaded.']);
            }
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(', ', $messages);

            return response()->json(['success' => false, 'msg' => $errorMsg]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'A required model was not found. Please check your data and try again.']);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'A database error occurred. Please check your query or constraints.']);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'An unexpected error occurred. Please try again later.']);
        }
    }

    // Function to update reading file
    public function updateFileReading(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate request data
            $validated = $request->validate([
                'edit_batch_name' => 'required|string|exists:batches,name',
                'edit_image_file_id' => 'required|integer|exists:readings,id',
                'edit_image_file_part' => 'required|integer|exists:readings,part',
                'edit_image_file_reading' => 'required|file|mimes:jpg,png'
            ]);

            // Check for uploaded file
            if (!$request->hasFile('edit_image_file_reading')) {
                return response()->json(['success' => false, 'msg' => 'No image file uploaded'], 400);
            }

            $imageFile = $validated['edit_image_file_reading'];
            $reading_quest_file = $request->file('edit_image_file_reading');
            $fileName = $reading_quest_file->getClientOriginalName();

            // Validate file format
            if ($reading_quest_file->isValid()) {
                $batch_filename = explode('_', $fileName);
                $part = explode('.', explode('_', $fileName)[4])[0];

                if (!(count($batch_filename) == 5 && $batch_filename[0] == 'reading' && $batch_filename[1] == 'toefl' && $part == $validated['edit_image_file_part'])) {
                    return response()->json(['success' => false, 'msg' => 'Image filename does not match the expected format: ' . $fileName]);
                }
            } else {
                return response()->json(['success' => false, 'msg' => 'Uploaded file is not valid: ' . $fileName]);
            }

            $newFileName = $imageFile->getClientOriginalName();
            $new_size = isset($newFileName) ? $imageFile->getSize() : 'Unknown';
            $extractedFileName = explode('_', $newFileName);
            $newBatchName = str_replace(' ', '_', $validated['edit_batch_name']);
            $userBatchFolder = 'user_' . Auth::id() . '/' . $newBatchName;
            $newBatchNameFile = ucwords($extractedFileName[2] . '_' . $extractedFileName[3]);
            $newPartFile = explode('.', $extractedFileName[4])[0];
            $oldFileId = $validated['edit_image_file_id'];
            $oldFileReading = Reading::findOrFail($oldFileId);
            $oldPartFileName = $oldFileReading->part;
            $pathOldFileImage = $oldFileReading->image_path;

            // Verify file naming consistency
            if ($newBatchNameFile == explode('/', $userBatchFolder)[1] && $newPartFile == $oldPartFileName) {
                try {
                    // Delete old file if it exists
                    if (Storage::disk('local')->exists($pathOldFileImage)) {
                        Storage::disk('local')->delete($pathOldFileImage);
                    }
                } catch (Exception $e) {
                    return response()->json(['success' => false, 'msg' => 'Failed to delete old file: ' . $e->getMessage()]);
                }

                try {
                    // Store new file and update reading record
                    $filePath = $imageFile->storeAs("images/{$newBatchNameFile}", $newFileName);
                    $oldFileReading->name = $newFileName;
                    $oldFileReading->image_path = $filePath;
                    $oldFileReading->size = $new_size;
                    $oldFileReading->update();
                } catch (Exception $e) {
                    return response()->json(['success' => false, 'msg' => 'Failed to update new file: ' . $e->getMessage()]);
                }

                DB::commit();

                return response()->json(['success' => true, 'msg' => "Success update image file: {$newFileName}"]);
            } else {
                return response()->json(['success' => false, 'msg' => 'Image filename does not match the expected format: ' . $newFileName], 422);
            }
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(', ', $messages);

            return response()->json(['success' => false, 'msg' => $errorMsg]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'The specified audio file or question model was not found.']);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'A database error occurred. Please try again later.']);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'An unexpected error occurred. Please try again later.']);
        }
    }

    // Function to add reading question
    public function addReadingQuest(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate request data
            $validated = $request->validate([
                'image_file_id_manual' => 'required|integer|exists:readings,id',
                'reading_quest' => 'required|string',
                'option_ans_1' => 'required|string',
                'option_ans_2' => 'required|string',
                'option_ans_3' => 'required|string',
                'option_ans_4' => 'required|string',
                'ans_correct' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($request) {
                        $validOptions = [
                            $request->input('option_ans_1'),
                            $request->input('option_ans_2'),
                            $request->input('option_ans_3'),
                            $request->input('option_ans_4')
                        ];
                        if (!in_array($value, $validOptions)) {
                            $fail('The correct answer must be one of the provided options.');
                        }
                    }
                ]
            ], [
                'image_file_id_manual.required' => 'Image file ID is required.',
                'image_file_id_manual.integer' => 'Image file ID must be an integer.',
                'image_file_id_manual.exists' => 'The selected image file does not exist.',
                'reading_quest.required' => 'Reading question is required.',
                'reading_quest.string' => 'Reading question must be a string.',
                'option_ans_1.required' => 'Option answer 1 is required.',
                'option_ans_1.string' => 'Option answer 1 must be a string.',
                'option_ans_2.required' => 'Option answer 2 is required.',
                'option_ans_2.string' => 'Option answer 2 must be a string.',
                'option_ans_3.required' => 'Option answer 3 is required.',
                'option_ans_3.string' => 'Option answer 3 must be a string.',
                'option_ans_4.required' => 'Option answer 4 is required.',
                'option_ans_4.string' => 'Option answer 4 must be a string.',
                'ans_correct.required' => 'The correct answer is required.',
                'ans_correct.string' => 'The correct answer must be a string.'
            ]);

            // Create new reading question
            $question = ReadingQuestion::create([
                'question' => $validated['reading_quest'],
                'option_1' => $validated['option_ans_1'],
                'option_2' => $validated['option_ans_2'],
                'option_3' => $validated['option_ans_3'],
                'option_4' => $validated['option_ans_4'],
                'ans_correct' => $validated['ans_correct']
            ]);

            // Attach question to reading
            Reading::findOrFail($validated['image_file_id_manual'])->questions()->attach($question->id);

            DB::commit();

            return response()->json(['success' => true, 'msg' => 'Question added successfully.']);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(', ', $messages);

            return response()->json(['success' => false, 'msg' => $errorMsg]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'The specified audio file or question model was not found.']);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'A database error occurred. Please try again later.']);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'An unexpected error occurred. Please try again later.']);
        }
    }

    // Function to add reading question file
    public function addReadingQuestFile(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate request data
            $validated = $request->validate([
                'image_file_id_file' => 'required|integer|exists:readings,id',
                'image_file_id_batch' => 'required|integer|exists:batches,id',
                'image_file_id_part' => 'required|integer|exists:readings,part',
                'quest_file' => 'required|file|mimes:xlsx|max:2048'
            ], [
                'image_file_id_file.required' => 'Image file ID (file) is required.',
                'image_file_id_file.integer' => 'Image file ID (file) must be an integer.',
                'image_file_id_file.exists' => 'The selected image file ID (file) does not exist.',
                'image_file_id_batch.required' => 'Image file ID (batch) is required.',
                'image_file_id_batch.integer' => 'Image file ID (batch) must be an integer.',
                'image_file_id_batch.exists' => 'The selected image file ID (batch) does not exist.',
                'image_file_id_part.required' => 'Image file ID (part) is required.',
                'image_file_id_part.integer' => 'Image file ID (part) must be an integer.',
                'image_file_id_part.exists' => 'The selected image file ID (part) does not exist.',
                'quest_file.required' => 'Quest file is required.',
                'quest_file.file' => 'Quest file must be a valid file.',
                'quest_file.mimes' => 'Quest file must be of type xlsx.',
                'quest_file.max' => 'Quest file size may not be greater than 10MB.'
            ]);

            $excel_file = $validated['quest_file'];

            if ($excel_file) {
                Log::info('File upload attempt (excel: reading):', ['file' => $excel_file]);
                Log::info('Excel file details:', [
                    'path' => $excel_file->getPathname(),
                    'size' => $excel_file->getSize(),
                    'mime' => $excel_file->getMimeType(),
                    'readable' => is_readable($excel_file->getPathname()),
                ]);
            }

            // Check for uploaded Excel file
            if ($excel_file) {
                $reading_quest_file = $request->file('quest_file');
                $batch_id = $validated['image_file_id_batch'];
                $batch = Batch::select('name')->where('id', $batch_id)->first();

                if ($batch) {
                    $batch_name = $batch->name;
                }

                $userBatchFolder = 'user_' . Auth::id() . '/' . str_replace(' ', '_', $batch_name);

                // Validate file format
                if ($excel_file->isValid()) {
                    $filename = $reading_quest_file->getClientOriginalName();
                    $batch_filename = explode('_', $filename);
                    $part = explode('.', $batch_filename[3])[0];
                    $expected_file_format = 'reading_batch_x_partX';

                    if (count($batch_filename) == 4 && $batch_filename[0] == 'reading' && ucwords($batch_filename[1] . '_' . $batch_filename[2]) == explode('/', $userBatchFolder)[1] && $part == $validated['image_file_id_part']) {
                        try {
                            // Import Excel data
                            $import = new SingleReadingQuestImport($validated['image_file_id_file']);

                            Excel::import($import, $excel_file);
                        } catch (LaravelExcelException $e) {
                            DB::rollBack();

                            return response()->json(['success' => false, 'msg' => 'Excel package error: ' . $e->getMessage()]);
                        } catch (ValidationException $e) {
                            DB::rollBack();

                            return response()->json(['success' => false, 'msg' => 'Validation error: ' . $e->getMessage()]);
                        } catch (Exception $e) {
                            DB::rollBack();

                            return response()->json(['success' => false, 'msg' => 'Failed to import Excel file: ' . $e->getMessage()]);
                        }

                        DB::commit();

                        return response()->json(['success' => true, 'msg' => 'Question imported successfully.']);
                    } else {
                        DB::rollBack();

                        return response()->json(['success' => false, 'msg' => 'Excel filename does not match the expected format: ' . $expected_file_format]);
                    }
                } else {
                    DB::rollBack();

                    return response()->json(['success' => false, 'msg' => 'Uploaded file is not valid.']);
                }
            } else {
                DB::rollBack();

                return response()->json(['success' => false, 'msg' => 'No Excel file were uploaded.']);
            }
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(', ', $messages);

            return response()->json(['success' => false, 'msg' => $errorMsg]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'The specified audio file or question model was not found.']);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'A database error occurred. Please try again later.']);
        } catch (PostTooLargeException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'Uploaded file is too large. Please upload a file smaller than 10MB.']);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'An unexpected error occurred: ' . $e->getMessage()]);
        }
    }

    // Function to edit reading question
    public function editReadingQuest(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate request data
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|exists:reading_questions,id',
                'edit_reading_quest' => 'required|string',
                'edit_option_ans_1' => 'required|string',
                'edit_option_ans_2' => 'required|string',
                'edit_option_ans_3' => 'required|string',
                'edit_option_ans_4' => 'required|string',
                'edit_ans_correct' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($request) {
                        $validOptions = [
                            $request->input('edit_option_ans_1'),
                            $request->input('edit_option_ans_2'),
                            $request->input('edit_option_ans_3'),
                            $request->input('edit_option_ans_4')
                        ];

                        if (!in_array($value, $validOptions)) {
                            $fail('The correct answer must be one of the provided options.');
                        }
                    }
                ]
            ], [
                'id.required' => 'The question ID is required.',
                'id.integer' => 'The question ID must be an integer.',
                'id.exists' => 'The selected question does not exist.',
                'edit_reading_quest.required' => 'The reading question is required.',
                'edit_reading_quest.string' => 'The reading question must be a string.',
                'edit_option_ans_1.required' => 'Option 1 is required.',
                'edit_option_ans_1.string' => 'Option 1 must be a string.',
                'edit_option_ans_2.required' => 'Option 2 is required.',
                'edit_option_ans_2.string' => 'Option 2 must be a string.',
                'edit_option_ans_3.required' => 'Option 3 is required.',
                'edit_option_ans_3.string' => 'Option 3 must be a string.',
                'edit_option_ans_4.required' => 'Option 4 is required.',
                'edit_option_ans_4.string' => 'Option 4 must be a string.',
                'edit_ans_correct.required' => 'The correct answer is required.',
                'edit_ans_correct.string' => 'The correct answer must be a string.'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()]);
            }

            $validated = $validator->validated();

            // Update reading question
            $reading_question = ReadingQuestion::findOrFail($validated['id']);
            $reading_question->update([
                'question' => $validated['edit_reading_quest'],
                'option_1' => $validated['edit_option_ans_1'],
                'option_2' => $validated['edit_option_ans_2'],
                'option_3' => $validated['edit_option_ans_3'],
                'option_4' => $validated['edit_option_ans_4'],
                'ans_correct' => $validated['edit_ans_correct']
            ]);

            DB::commit();

            return response()->json(['success' => true, 'msg' => 'Question updated successfully.']);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(', ', $messages);

            return response()->json(['success' => false, 'msg' => $errorMsg]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'The specified audio file or question model was not found.']);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'A database error occurred. Please try again later.']);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'errors' => ['general' => 'An unexpected error occurred. Please try again later.', 'exception' => $e->getMessage()]]);
        }
    }

    // Function to delete reading question
    public function deleteReadingQuest(Request $request)
    {
        try {
            DB::beginTransaction();
            // Validate request data
            $validated = $request->validate([
                'id' => 'required|integer|exists:reading_questions,id'
            ], [
                'id.required' => 'ID is required.',
                'id.integer' => 'ID must be an integer.',
                'id.exists' => 'The selected ID does not exist in reading_questions.'
            ]);

            // Delete reading question
            $reading_question = ReadingQuestion::findOrFail($validated['id']);
            $reading_question->delete();

            DB::commit();

            return response()->json(['success' => true, 'msg' => 'Question deleted successfully.']);
        } catch (ValidationException $e) {
            DB::rollBack();

            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(', ', $messages);

            return response()->json(['success' => false, 'msg' => $errorMsg]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'The specified audio file or question model was not found.']);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'msg' => 'A database error occurred. Please try again later.']);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'errors' => ['general' => 'An unexpected error occurred. Please try again later.', 'exception' => $e->getMessage()]]);
        }
    }
}
