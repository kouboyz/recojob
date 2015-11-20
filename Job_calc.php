<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Job_calc {

	/**
	 * 通常時間を取得します。
	 *
	 * @param string $total
	 * @param float $regular_time
	 * @return float
	 */
	public function get_job_time($total, $regular_time)
	{
		// 定時を超えているか
		if ($total > $regular_time)
		{
			return $regular_time;
		}
		else
		{
			return $total;
		}
	}


	/**
	 * 残業時間を取得します。
	 *
	 * @param float $total
	 * @param float $regular_time
	 * @param float $night_time
	 * @return float
	 */
	public function get_over_time($total, $regular_time, $night_time)
	{
		// 定時を超えているか
		if ($total <= $regular_time)
		{
			return 0;
		}

		return ($total - $regular_time - $night_time);
	}


	/**
	 * 深夜時間を取得します。
	 *
	 * @param string $start
	 * @param string $end
	 * @param string $night_start
	 * @param string $night_end
	 */
	public function get_night_time($start, $end, $night_start, $night_end)
	{
		$total = 0;
		$start_time = strtotime($start);
		$end_time = strtotime($end);
		$night_start_time = strtotime($night_start);
		$night_end_time = strtotime($night_end);

		if ($start == '0:00' && $end == '0:00')
		{
			// 有休、振休、欠勤など
			return $total;
		}
		else if ($night_end_time <= $start_time && $end_time <= $night_start_time)
		{
			// 深夜時間の勤務がない
			return $total;
		}
		else if ($night_start == '0:00' && $night_end == '0:00')
		{
			// 深夜時間が設定されていない
			return $total;
		}

		// 日付をまたぐ場合は、2回に分けて計算する
		if ($start_time > $end_time)
		{
			if ($start_time < $night_start_time)
			{
				// 深夜開始時間より前から勤務開始している場合
				$total += (strtotime('24:00') - $night_start_time) / 3600;
			}
			else
			{
				// 深夜開始時間より後から勤務開始している場合
				$total += (strtotime('24:00') - $start_time) / 3600;
			}

			if ($end_time < $night_end_time)
			{
				// 深夜終了時間より前に勤務終了している場合
				$total += ($end_time - strtotime('00:00')) / 3600;
			}
			else
			{
				// 深夜終了時間より後に勤務終了している場合
				$total += ($night_end_time - strtotime('00:00')) / 3600;
			}
		}
		else
		{
			// 深夜終了時間より前に勤務開始している場合
			if ($start_time <= $night_end_time)
			{
				if ($night_end_time <= $end_time)
				{
					// 深夜終了時間より後に勤務終了している場合
					$total += ($night_end_time - $start_time) / 3600;
				}
				else
				{
					// 深夜終了時間より前に勤務終了している場合
					$total += ($end_time - $start_time) / 3600;
				}
			}

			// 深夜終了時間より後に勤務開始している場合
			if ($night_end_time <= $start_time)
			{
				if ($start_time <= $night_start_time)
				{
					// 深夜開始時間より前に勤務開始している場合
					$total += ($end_time - $night_start_time) / 3600;
				}
				else
				{
					// 深夜開始時間より後に勤務開始している場合
					$total += ($end_time - $start_time) / 3600;
				}
			}
		}

		return $total;
	}


	/**
	 * 合計勤務時間を取得します。
	 *
	 * @param string $start
	 * @param string $end
	 * @return float
	 */
	public function get_total_job_time($start, $end, $break_time)
	{
		$total = 0;

		// 有休、振休、欠勤など
		if ($start == '0:00' && $end == '0:00') {
			return $total;
		}

		$start_time = strtotime($start);
		$end_time = strtotime($end);

		// 日付をまたぐ場合は、2回に分けて計算する
		if ($start_time > $end_time) {
			$total += (strtotime('24:00') - $start_time) / 3600;
			$total += ($end_time - strtotime('00:00')) / 3600;
		} else {
			$total = ($end_time - $start_time) / 3600;
		}

		// 休憩時間
		$total -= $break_time;

		return $total;
	}
}
