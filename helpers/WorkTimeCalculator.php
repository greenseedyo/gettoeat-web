<?php
namespace Helpers;

use Datetime;

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

    public function getRecord(Datetime $business_date, string $staff_name)
    {
        $key = sprintf("%s-%s", $business_date->format('Ymd'), $staff_name);
        if ($record = $this->records[$key]) {
            return $record;
        } else {
            $record = new WorkTimeRecord($business_date, $staff_name);
            $this->records[$key] = $record;
            return $record;
        }
    }

    public function getWorkTimeRecords()
    {
        foreach ($this->logs as $log) {
            $business_date = $log->getBusinessDate();
            $staff_name = $log->getStaffName();
            $record = $this->getRecord($business_date, $staff_name);
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
        return $this->records;
    }
}


class WorkTimeLog
{
    protected $staff_name;
    protected $business_date;
    protected $punch_time;
    protected $punch_type;

    const TYPE_IN = 1;
    const TYPE_OUT = 2;

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


class WorkTimeRecord
{
    protected $business_date;
    protected $staff_name;
    protected $punch_in;
    protected $punch_out;

    public function __construct(Datetime $business_date, string $staff_name)
    {
        $this->business_date = $business_date;
        $this->staff_name = $staff_name;
    }

    public function setPunchIn(Datetime $punch_in)
    {
        $this->punch_in = $punch_in;
    }

    public function setPunchOut(Datetime $punch_out)
    {
        $this->punch_out = $punch_out;
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

    public function getWorkTime(): string
    {
        if (!$this->punch_out or !$this->punch_in) {
            return '';
        }
        $interval = $this->punch_out->diff($this->punch_in);
        return $interval->format("%H:%I:%S");
    }
}
