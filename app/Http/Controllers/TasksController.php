<?php

namespace OSRSCM\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OSRSCM\Task;
use OSRSCM\UserTask;

class TasksController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the tasks dashboard.
     *
     * @return
     */
    public function index() {
        $currentUserTasks = UserTask::with('task')->where('user_id', Auth::user()->id)->where('status', 'incomplete')->limit(3)->get();

        $completedUserTasks = UserTask::with('task')->where('user_id', Auth::user()->id)->where('status', 'complete')->orderBy('updated_at', 'DESC')->get();

        $completedUserTasksEasy = count(UserTask::with('task')->where('user_id', Auth::user()->id)->where('status', 'complete')->whereHas('task', function ($query) {
            $query->where('difficulty', '=', 'easy');
        })->get());
        $completedUserTasksMedium = count(UserTask::with('task')->where('user_id', Auth::user()->id)->where('status', 'complete')->whereHas('task', function ($query) {
            $query->where('difficulty', '=', 'medium');
        })->get());
        $completedUserTasksHard = count(UserTask::with('task')->where('user_id', Auth::user()->id)->where('status', 'complete')->whereHas('task', function ($query) {
            $query->where('difficulty', '=', 'hard');
        })->get());
        $completedUserTasksElite = count(UserTask::with('task')->where('user_id', Auth::user()->id)->where('status', 'complete')->whereHas('task', function ($query) {
            $query->where('difficulty', '=', 'elite');
        })->get());

        $easyTaskAmount = count(Task::where('difficulty', 'easy')->get());
        $mediumTaskAmount = count(Task::where('difficulty', 'medium')->get());
        $hardTaskAmount = count(Task::where('difficulty', 'hard')->get());
        $eliteTaskAmount = count(Task::where('difficulty', 'elite')->get());

        $easyProgress = ($completedUserTasksEasy / 100) * $easyTaskAmount;
        $mediumProgress = ($completedUserTasksMedium / 100) * $mediumTaskAmount;
        $hardProgress = ($completedUserTasksHard / 100) * $hardTaskAmount;
        $eliteProgress = ($completedUserTasksElite / 100) * $eliteTaskAmount;

        return view('task', compact('currentUserTasks', 'completedUserTasks', 'easyProgress', 'mediumProgress', 'hardProgress', 'eliteProgress'));
    }

    /**
     * Generates a new task for user.
     *
     * @return
     */
    public function store() {
        $userTasks = UserTask::with('task')->where('user_id', Auth::user()->id)->where('status', 'incomplete')->get();

        if (count($userTasks) <= 2) {
            $randomTask = Task::doesntHave('userTask')->inRandomOrder()->first();

            UserTask::create([
                'user_id' => Auth::user()->id,
                'task_id' => $randomTask['id'],
                'status' => 'incomplete',
            ]);

            return redirect(route('task'))->with('message', 'Task generated!');
        } else {
            return redirect()->back()->withErrors(['You have reached the limit of three tasks!']);
        }
    }
}
