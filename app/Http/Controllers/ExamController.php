<?php

namespace App\Http\Controllers;

use App\Models\{
    ExamTimer,
    UserExamLink,
};
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ExamController extends Controller
{
    // Function to save or update the remaining time for an exam
    public function saveOrUpdateRemainingTime(Request $request)
    {
        try {
            $validated = $request->validate([
                "exam_id" => "required|integer|exists:exams,id",
                "remaining_time" => "required|date_format:H:i:s"
            ], [
                "exam_id.required" => "Exam ID is required.",
                "exam_id.integer" => "Exam ID must be an integer.",
                "exam_id.exists" => "The selected exam does not exist.",
                "remaining_time.required" => "Remaining time is required.",
                "remaining_time.date_format" => "Remaining time must be in the format H:i:s."
            ]);
            $user_id = Auth::id();

            ExamTimer::updateOrCreate([
                "user_id" => $user_id,
                "exam_id" => $validated["exam_id"]
            ], [
                "remaining_time" => $validated["remaining_time"]
            ]);

            return response()->json([
                "success" => true,
                "message" => "Remaining time saved successfully."
            ]);
        } catch (ValidationException $e) {
            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json([
                "success" => false,
                "message" => $errorMsg
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "An unexpected error occurred. Please try again later."
            ]);
        }
    }

    // Function to update the current part of an exam for a user
    public function updateCurrentPart(Request $request)
    {
        try {
            $validated = $request->validate([
                "user_id" => "required|integer|exists:users,id",
                "exam_id" => "required|integer|exists:exams,id",
                "current_part" => "required|integer"
            ], [
                "user_id.required" => "User ID is required.",
                "user_id.integer" => "User ID must be an integer.",
                "user_id.exists" => "The selected user does not exist.",
                "exam_id.required" => "Exam ID is required.",
                "exam_id.integer" => "Exam ID must be an integer.",
                "exam_id.exists" => "The selected exam does not exist.",
                "current_part.required" => "Current part is required.",
                "current_part.integer" => "Current part must be an integer."
            ]);
            $userExamLink = UserExamLink::where("user_id", $validated["user_id"])
                ->where("exam_id", $validated["exam_id"])
                ->first();

            if ($userExamLink) {
                $userExamLink->current_part = $validated["current_part"];
                $userExamLink->save();

                return response()->json([
                    "success" => true,
                    "message" => "Current part updated successfully."
                ]);
            }

            return response()->json([
                "success" => false,
                "message" => "User exam link not found."
            ]);
        } catch (ValidationException $e) {
            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json([
                "success" => false,
                "message" => $errorMsg
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "An unexpected error occurred. Please try again later."
            ]);
        }
    }
}
