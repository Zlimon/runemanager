<?php

namespace RuneManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuneManager\Task;
use RuneManager\AccountTask;
use RuneManager\Helpers\Helper;

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
        $currentAccountTasks = AccountTask::with('task')->where('account_id', Helper::sessionAccountId())->where('status', 'incomplete')->limit(3)->get();

        $completedAccountTasks = AccountTask::with('task')->where('account_id', Helper::sessionAccountId())->where('status', 'complete')->orderBy('updated_at', 'DESC')->get();

        $completedAccountTasksEasy = count(AccountTask::with('task')->where('account_id', Helper::sessionAccountId())->where('status', 'complete')->whereHas('task', function ($query) {
            $query->where('difficulty', '=', 'easy');
        })->get());
        $completedAccountTasksMedium = count(AccountTask::with('task')->where('account_id', Helper::sessionAccountId())->where('status', 'complete')->whereHas('task', function ($query) {
            $query->where('difficulty', '=', 'medium');
        })->get());
        $completedAccountTasksHard = count(AccountTask::with('task')->where('account_id', Helper::sessionAccountId())->where('status', 'complete')->whereHas('task', function ($query) {
            $query->where('difficulty', '=', 'hard');
        })->get());
        $completedAccountTasksElite = count(AccountTask::with('task')->where('account_id', Helper::sessionAccountId())->where('status', 'complete')->whereHas('task', function ($query) {
            $query->where('difficulty', '=', 'elite');
        })->get());

        $easyTaskAmount = count(Task::where('difficulty', 'easy')->get());
        $mediumTaskAmount = count(Task::where('difficulty', 'medium')->get());
        $hardTaskAmount = count(Task::where('difficulty', 'hard')->get());
        $eliteTaskAmount = count(Task::where('difficulty', 'elite')->get());

        $easyProgress = ($completedAccountTasksEasy / 100) * $easyTaskAmount;
        $mediumProgress = ($completedAccountTasksMedium / 100) * $mediumTaskAmount;
        $hardProgress = ($completedAccountTasksHard / 100) * $hardTaskAmount;
        $eliteProgress = ($completedAccountTasksElite / 100) * $eliteTaskAmount;

        return view('task', compact('currentAccountTasks', 'completedAccountTasks', 'easyProgress', 'mediumProgress', 'hardProgress', 'eliteProgress'));
    }

    /**
     * Generates a new task for user.
     *
     * @return
     */
    public function store() {
        $AccountTasks = AccountTask::with('task')->where('account_id', Helper::sessionAccountId())->where('status', 'incomplete')->get();

        if (count($AccountTasks) <= 2) {
            $randomTask = Task::doesntHave('AccountTask')->inRandomOrder()->first();

            AccountTask::create([
                'account_id' => Helper::sessionAccountId(),
                'task_id' => $randomTask['id'],
                'status' => 'incomplete',
            ]);

            return redirect(route('task'))->with('message', 'Task generated!');
        } else {
            return redirect()->back()->withErrors(['You have reached the limit of three tasks!']);
        }
    }
}
