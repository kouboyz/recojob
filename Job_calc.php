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

		if ($start == '00:00' && $end == '00:00')
		{
			// 有休、振休、欠勤など
			return $total;
		}
// 		else if (!(($start <= $night_start && $night_start <= $end) ||
// 				   ($start <= $night_end   && $night_end   <= $end) ||
// 				   ($night_start <= $start && $start <= $night_end) ||
// 				   ($night_start <= $end   && $end   <= $night_end)))
// 		{
// 			// [tips] phpは文字列で時刻の比較が可能
// 			// 深夜時間の勤務がない
// 			return $total;
//		}
		else if ($night_start == '00:00' && $night_end == '00:00')
		{
			// 深夜時間が設定されていない
			return $total;
		}

		// ------------------------------------------------
		// 日付を超える場合
		// ------------------------------------------------
		if ($start > $end)
		{
			// 深夜終了時間より前に勤務開始している場合
// 			if (($night_start >  $night_end && $start < '24:00' && '00:00' < $night_end) ||	// 深夜枠が日付を超える場合
// 				($night_start <= $night_end && $start < $night_end))						// 深夜枠が日付を超えない場合
			if ($start < $night_end)
			{
				if ($night_end <= $end)
				{
					// 深夜終了時間より後に勤務終了している場合
					$total += self::_time_diff($start, $night_end);
				}
				else
				{
					// 深夜終了時間より前に勤務終了している場合
					$total += self::_time_diff($start, $end);
				}
			}

			// 深夜開始時間より後に勤務終了している場合
// 			if (($night_start >  $night_end && $night_start < '24:00' && '00:00' < $end) ||	// 深夜枠が日付を超える場合
// 				($night_start <= $night_end && $night_start < $end))
			if ($night_start < $end)
			{
				if ($start <= $night_start)
				{
					// 深夜開始時間より前に勤務開始している場合
					$total += self::_time_diff($night_start, $end);
				}
				else
				{
					// 深夜開始時間より後に勤務開始している場合
					$total += self::_time_diff($start, $end);
				}
			}
		}
		// ------------------------------------------------
		// 日付を超えない場合
		// ------------------------------------------------
		else
		{
			// 深夜終了時間より前に勤務開始している場合
			if ($start < $night_end)
			{
				if ($night_end <= $end)
				{
					// 深夜終了時間より後に勤務終了している場合
					$total += self::_time_diff($start, $night_end);
				}
				else
				{
					// 深夜終了時間より前に勤務終了している場合
					$total += self::_time_diff($start, $end);
				}
			}

			// 深夜開始時間より後に勤務終了している場合
			if ($night_start < $end)
			{
				if ($start <= $night_start)
				{
					// 深夜開始時間より前に勤務開始している場合
					$total += self::_time_diff($night_start, $end);
				}
				else
				{
					// 深夜開始時間より後に勤務開始している場合
					$total += self::_time_diff($start, $end);
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

	/**
	 * 時刻の差を返却します。
	 * 日付を超える場合も考慮します。
	 *
	 * @param string $start
	 * @param string $end
	 */
	function _time_diff($start, $end)
	{
		$total = 0;
	// 日付をまたぐ場合は、2回に分けて計算する
		if ($start > $end) {
			$total += (strtotime('24:00') - strtotime($start))  / (60 * 60);
			$total += (strtotime($end)    - strtotime('00:00')) / (60 * 60);
		} else {
			$total = (strtotime($end) - strtotime($start)) / (60 * 60);
		}
		return $total;
	}
}
