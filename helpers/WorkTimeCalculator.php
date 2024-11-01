<?php
namespace Helpers;

use Datetime;
use DateInterval;
use UnexpectedValueException;

class WorkTimeCalculator
{
    protected $logs = array();
    protected $records = array();

    public function getTypeText(int $type): string
    {
        switch ($type) {
        case PunchLog::TYPE_IN:
            return "上班";
            break;
        case PunchLog::TYPE_OUT:
            return "下班";
            break;
        }
    }

    public function add(WorkTimeLog $log)
    {
        $this->logs[] = $log;
    }

    public function getRecord(Datetime $business_date, string $staff_id)
    {
        $key = sprintf("%s-%s", $business_date->format('Ymd'), $staff_id);
        if ($record = $this->records[$key] ?? false) {
            return $record;
        } else {
            $record = new CombinedWorkTimeRecord($business_date, $staff_id);
            $this->records[$key] = $record;
            return $record;
        }
    }

    public function combineWorkTimeRecords()
    {
        foreach ($this->logs as $log) {
            $business_date = $log->getBusinessDate();
            $staff_id = $log->getStaffId();
            $record = $this->getRecord($business_date, $staff_id);
            $record->setStaffName($log->getStaffName());
            $punch_time = $log->getPunchTime();
            switch ($log->getPunchType()) {
            case WorkTimeLog::TYPE_IN:
                $record->setPunchIn($punch_time);
                break;
            case WorkTimeLog::TYPE_OUT:
                $record->setPunchOut($punch_time);
                break;
            }
        }
    }

    public function getRecords(): array
    {
        return $this->records ?: array();
    }

    public function getTotalWorkTime(): string
    {
        $end = new DateTime('00:00');
        $start = clone $end;
        foreach ($this->getRecords() as $record) {
            try {
                $interval = $record->getWorkTimeInterval();
            } catch (UnexpectedValueException $e) {
                continue;
            }
            $end->add($interval);
        }
        $diff = $end->diff($start);
        if ($diff->days > 0) {
            $hours = $diff->days * 24 + (int)$diff->format("%H:%I:%S");
            return sprintf('%s:%s', $hours, $diff->format("%I:%S"));
        } else {
            return $diff->format("%H:%I:%S");
        }
    }
}


class WorkTimeLog
{
    protected $staff_id;
    protected $staff_name;
    protected $business_date;
    protected $punch_time;
    protected $punch_type;

    const TYPE_IN = 1;
    const TYPE_OUT = 2;

    public function setStaffId(string $staff_id)
    {
        $this->staff_id = $staff_id;
        return $this;
    }

    public function setStaffName(string $staff_name)
    {
        $this->staff_name = $staff_name;
        return $this;
    }

    public function setBusinessDate(Datetime $business_date)
    {
        $this->business_date = $business_date;
        return $this;
    }

    public function setPunchType(int $punch_type)
    {
        if (!in_array($punch_type, array(self::TYPE_IN, self::TYPE_OUT))) {
            throw new \InvalidArgumentException('invalid punch type');
        }
        $this->punch_type = $punch_type;
        return $this;
    }

    public function setPunchTime(Datetime $punch_time)
    {
        $this->punch_time = $punch_time;
        return $this;
    }

    public function getStaffId(): int
    {
        return $this->staff_id;
    }

    public function getStaffName(): string
    {
        return $this->staff_name;
    }

    public function getBusinessDate(): Datetime
    {
        return $this->business_date;
    }

    public function getPunchType(): int
    {
        return $this->punch_type;
    }

    public function getPunchTime(): Datetime
    {
        return $this->punch_time;
    }
}


class CombinedWorkTimeRecord
{
    protected $business_date;
    protected $staff_id;
    protected $staff_name;
    protected $punch_in;
    protected $punch_out;

    public function __construct(Datetime $business_date, int $staff_id)
    {
        $this->business_date = $business_date;
        $this->staff_id = $staff_id;
    }

    public function setStaffName(string $staff_name)
    {
        $this->staff_name = $staff_name;
    }

    public function setPunchIn(Datetime $punch_in)
    {
        if (isset($this->punch_in) and $this->punch_in < $punch_in) {
            return;
        }
        $this->punch_in = $punch_in;
    }

    public function setPunchOut(Datetime $punch_out)
    {
        if (isset($this->punch_out) and $this->punch_out > $punch_out) {
            return;
        }
        $this->punch_out = $punch_out;
    }

    public function getStaffId(): int
    {
        return $this->staff_id;
    }

    public function getStaffName(): string
    {
        return $this->staff_name;
    }

    public function getFormattedBusinessDate(string $format): string
    {
        return $this->business_date->format($format);
    }

    public function getFormattedPunchIn(string $format): string
    {
        return ($this->punch_in ? $this->punch_in->format($format) : '');
    }

    public function getFormattedPunchOut(string $format): string
    {
        return ($this->punch_out ? $this->punch_out->format($format) : '');
    }

    public function getWorkTimeInterval(): DateInterval
    {
        if (!$this->punch_out) {
            throw new PunchOutNotFoundException;
        }
        if (!$this->punch_in) {
            throw new PunchInNotFoundException;
        }
        $interval = $this->punch_out->diff($this->punch_in);
        if (0 === $interval->invert) {
            throw new InvalidDateIntervalException;
        }
        return $interval;
    }

    public function getFormattedWorkTime(): string
    {
        try {
            $interval = $this->getWorkTimeInterval();
        } catch (InvalidDateIntervalException $e) {
            return '打卡資訊有誤';
        } catch (PunchInNotFoundException|PunchOutNotFoundException $e) {
            return '打卡資訊不完整';
        }
        return $interval->format("%H:%I:%S");
    }
}


class PunchInNotFoundException extends UnexpectedValueException
{
}

class PunchOutNotFoundException extends UnexpectedValueException
{
}

class InvalidDateIntervalException extends UnexpectedValueException
{
}
