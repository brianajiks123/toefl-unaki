<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Hash,
    Auth,
    Log,
};
use Illuminate\Support\Str;
use App\Http\Controllers\SendMailController;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Function to log out the user and clear the session
    public function logout(Request $request)
    {
        if (Auth::check()) {
            $request->session()->flush();

            Auth::logout();
        }

        return redirect("/");
    }

    // Function to verify user email using token
    public function verify($token)
    {
        try {
            $user = User::where("remember_token", $token)->firstOrFail();
            $user->email_verified_at = now();
            $user->remember_token = Str::random(60);
            $user->save();

            return redirect("/")->with("success", "Your account has been verified.");
        } catch (ModelNotFoundException $e) {
            return redirect("/error-404");
        } catch (Exception $e) {
            return response()->json(array("success" => false, "msg" => "An unexpected error occurred. Please try again later."), 500);
        }
    }

    // Function to load the login page or redirect based on user role
    public function loadLogin()
    {
        $title = "Login | " . config("app.name");

        if (Auth::check()) {
            switch (Auth::user()->is_admin) {
                case 1:
                    return redirect("/admin/dashboard");
                case 2:
                    return redirect("/lecturer/dashboard");
                case 0:
                    return redirect("/student/exams");
            }
        }

        return view("auth.login", compact("title"));
    }

    // Function to handle user login
    public function userLogin(Request $request)
    {
        try {
            $validated = $request->validate(array(
                "email" => "required|string|exists:users,email",
                "password" => "required|string"
            ), array(
                "email.required" => "Name is required.",
                "email.string" => "Email must be a string.",
                "email.exists" => "Email is not registered.",
                "password.required" => "Password is required.",
                "password.string" => "Password must be a string."
            ));

            if (Auth::attempt($validated)) {
                $user = Auth::user();

                if (is_null($user->email_verified_at)) {
                    Auth::logout();

                    return response()->json(array("success" => false, "msg" => "Your account is not verified! Please check your email for verification."));
                }

                $route = $user->is_admin == 1 ? "/admin/dashboard" : ($user->is_admin == 2 ? "/lecturer/dashboard" : "/student/exams");

                return response()->json(array("success" => true, "msg" => "Login success", "route" => $route));
            }

            return response()->json(array("success" => false, "msg" => "Password is incorrect!"));
        } catch (ValidationException $e) {
            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return response()->json(array("success" => false, "msg" => $errorMsg));
        } catch (Exception $e) {
            return response()->json(array("success" => false, "msg" => "An unexpected error occurred. Please try again later."));
        }
    }

    // Function to load the forget password page or redirect based on user role
    public function forgetPasswdLoad()
    {
        $title = "Forget Password | " . config("app.name");

        if (Auth::check()) {
            switch (Auth::user()->is_admin) {
                case 1:
                    return redirect("/admin/dashboard");
                case 2:
                    return redirect("/lecturer/dashboard");
                case 0:
                    return redirect("/student/exams");
            }
        }

        return view("auth.forget_passwd", compact("title"));
    }

    // Function to handle forget password request
    public function forgetPasswd(Request $request)
    {
        try {
            $validatedData = $request->validate(array(
                "email" => "required|string|exists:users,email"
            ), array(
                "email.required" => "Name is required.",
                "email.string" => "Email must be a string.",
                "email.exists" => "Email is not registered."
            ));
            $user = User::where("email", $validatedData["email"])->firstOrFail();
            $subject = "Forget Password";
            $data = "
                <b>Hello {$user->name}.</b><br><br>Forget your password?<br>
                To reset your <b>" . config("app.name") . "</b> account password, click the button below.<br><br>
                <a href='" . url("reset/{$user->remember_token}") . "' style='border: 10px solid blue; border-radius:5px; background-color: blue; color: white; text-decoration:none;'>Reset your password</a><br><br>
                Thanks,<br>
                " . config("app.name") . "
            ";
            $sending_mail = new SendMailController();
            $sending_mail->sendMail($user->email, $subject, $data);

            return redirect()->back()->with("success", "Password reset link has been sent. Please check your email.");
        } catch (ValidationException $e) {
            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return redirect()->back()->with("error", $errorMsg);
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with("error", "Model not found in the system!");
        } catch (Exception $e) {
            return redirect()->back()->with("error", "An unexpected error occurred. Please try again later.");
        }
    }

    // Function to load the reset password page
    public function getReset($token)
    {
        try {
            $title = "Reset Password | " . config("app.name");
            $user = User::where("remember_token", $token)->firstOrFail();
            $data = array("token" => $token, "email" => $user->email);

            return view("auth.reset", array("title" => $title, "data" => $data));
        } catch (ModelNotFoundException $e) {
            Log::error("Reset password token not found: " . $token);

            abort(403);
        } catch (Exception $e) {
            Log::error("Unexpected error during reset password: " . $e->getMessage());

            return response()->json(array("success" => false, "msg" => "An unexpected error occurred. Please try again later."), 500);
        }
    }

    // Function to handle password reset
    public function postReset(Request $request, $token)
    {
        try {
            $validatedData = $request->validate(array(
                "email" => "required|string|exists:users,email",
                "password" => array(
                    "required",
                    "string",
                    "min:8",
                    "max:255",
                    "regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/"
                ),
                "password_confirmation" => array(
                    "required",
                    "string",
                    "min:8",
                    "max:255",
                    "regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/"
                )
            ), array(
                "email.required" => "Name is required.",
                "email.string" => "Email must be a string.",
                "email.exists" => "Email is not registered.",
                "password.required" => "Password is required.",
                "password.string" => "New password must be a string.",
                "password.min" => "New password must be at least 8 characters long.",
                "password.max" => "New password cannot exceed 255 characters.",
                "password.regex" => "New password must contain at least one uppercase letter, one lowercase letter, one digit, and one special character.",
                "password_confirmation.required" => "Password confirmation is required.",
                "password_confirmation.string" => "Password confirmation must be a string.",
                "password_confirmation.min" => "Password confirmation must be at least 8 characters long.",
                "password_confirmation.max" => "Password confirmation cannot exceed 255 characters.",
                "password_confirmation.regex" => "Password confirmation must contain at least one uppercase letter, one lowercase letter, one digit, and one special character."
            ));
            $user = User::where("email", $validatedData["email"])->where("remember_token", $token)->firstOrFail();
            $user->password = Hash::make($validatedData["password"]);
            $user->remember_token = Str::random(60);
            $user->save();

            return redirect("/")->with("success", "Successfully password reset.");
        } catch (ValidationException $e) {
            $messages = collect($e->errors())->flatten()->all();
            $errorMsg = implode(", ", $messages);

            return redirect()->back()->with("error", $errorMsg);
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with("error", "Model not found in the system!");
        } catch (Exception $e) {
            return redirect()->back()->with("error", "An unexpected error occurred. Please try again later.");
        }
    }
}
