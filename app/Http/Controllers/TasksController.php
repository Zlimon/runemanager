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
        if (Auth::user()->member->first()) {
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

            $easyProgress = round(($completedAccountTasksEasy / $easyTaskAmount) * 100);
            // $mediumProgress = round(($completedAccountTasksMedium / $mediumTaskAmount) * 100);
            // $hardProgress = round(($completedAccountTasksHard / $hardTaskAmount) * 100);
            // $eliteProgress = round(($completedAccountTasksElite / $eliteTaskAmount) * 100);
            $mediumProgress = 0;
            $hardProgress = 0;
            $eliteProgress = 0;

            return view('task', compact('currentAccountTasks', 'completedAccountTasks', 'easyProgress', 'mediumProgress', 'hardProgress', 'eliteProgress'));    
        } else {
            return redirect(route('create-member'))->withErrors(['You must link an Old School RuneScape account before you can be assigned tasks!']);
        }
    }

    /**
     * Generates a new task for user.
     *
     * @return
     */
    public function store() {
        if (count(AccountTask::with('task')->where('account_id', Helper::sessionAccountId())->where('status', 'incomplete')->get()) <= 2) {
            $randomTask = Task::doesntHave('AccountTask')->inRandomOrder()->first();

            if ($randomTask) {
                AccountTask::create([
                    'account_id' => Helper::sessionAccountId(),
                    'task_id' => $randomTask['id'],
                    'status' => 'incomplete',
                ]);

                return redirect(route('task'))->with('message', 'Task generated!');
            } else {
                return redirect()->back()->withErrors(['There are no more tasks for you to do!']);
            }
        } else {
            return redirect()->back()->withErrors(['You have reached the limit of three tasks!']);
        }
    }

    public function update(AccountTask $task) {
        $task = AccountTask::with('task')->where('account_id', Helper::sessionAccountId())->where('task_id', request('task_id'))->first();

        if ($task) {
            AccountTask::where('task_id', request('task_id'))->update(['status' => 'complete']);

            return redirect()->back()->with('message', 'Task "'.$task->task->task.'" complete! Reward: "'.$task->task->reward.'"');
        }
    }
}
