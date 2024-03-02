<?php

namespace App\Cronjobs;

use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Contracts\Database\Query\Builder;

final class GarbageCollectionCommand extends Command
{
	protected $signature = 'app:garbage-collect';

	public function __invoke()
	{
		Activity::query()
			->where(function (Builder $q): void {
				$q->whereNull('type')->orWhereNull('data');
			})
			->where('date', '<', Carbon::yesterday())
			->delete();
	}
}
