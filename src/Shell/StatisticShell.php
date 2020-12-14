<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Datasource\ConnectionManager;
use Cake\Log\Log;

/**
 * Statistic shell command.
 */
class StatisticShell extends Shell
{

    /**
     * Manage the available sub-commands along with their arguments and help
     *
     * @see http://book.cakephp.org/3.0/en/console-and-shells.html#configuring-options-and-generating-help
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        return $parser;
    }

    /**
     * main() method.
     *
     * @return bool|int|null Success or error code.
     */
    public function main()
    {
        $this->out('Start EmployeeReport Shell');
        $this->logConfig();
        Log::write('info', '-->>start cron ReportEmployees');

        $this->tableReportTodayInserted('ReportEmployees');
        $cronStatus = $this->statusCronJob('ReportEmployees',1);
        if($cronStatus) {
            $status = 'true';
        } else {
            $status = 'false';
        }

        Log::write('info', 'status cron: '.$status);
        if($cronStatus) {
            Log::write('info', 'run execute cron task');
            $academicPeriodId = $this->getAcademicPeriodId();
            Log::write('info', 'find academic period: ' .$academicPeriodId);
            $this->insertIntoReportEmployees($academicPeriodId);
            $this->setStatusCron( 'ReportEmployees', 1 );
        } else {
            Log::write('info', '--<<not execute cron task:' .$status."\n\n");
        }
        $this->out('End EmployeeReport Shell');
    }

    function tableReportTodayInserted($nameCron) {
        $connection = ConnectionManager::get('default');
        $currentDate = date('Y-m-d',time());
        $fullDate = date('Y-m-d H:i:s',time());
        $sql = "SELECT * FROM cron_job_report WHERE DATE_FORMAT(start,'%Y-%m-%d') = '{$currentDate}' AND name = '{$nameCron}'";
        Log::write('info', 'find cron job sql '. "\n".$sql );
        $result = $connection->execute($sql,[]);
        if(empty($result->fetchAll())) {
            Log::write('info', 'new cron task for today');
            $sql = "UPDATE cron_job_report SET start = '{$fullDate}', end = NULL, `count` = 0, status = 1 WHERE `name` = '{$nameCron}'";
            $connection->execute($sql,[]);
//            Log::write('info', 'update cron task for today'. "\n$sql");
            Log::write('info', 'update cron task for today');
        } else {
            Log::write('info', '--<<cron today worked');
        }
    }

    function setStatusCron( $nameCron, $status ) {
        $connection = ConnectionManager::get('default');
        $sql = "UPDATE cron_job_report SET status = {$status} WHERE `name` = '{$nameCron}'";
//        Log::write('info', 'update cron task status: '.$status." $nameCron". "\n$sql");
        Log::write('info', 'update cron task status: '.$status." $nameCron");
        $connection->execute($sql,[]);

        if($status == 2) {
            $this->setEndWorkCron($nameCron);
            Log::write('info', '--<<end cron ReportEmployees'."\n\n");
        }
    }

    function setEndWorkCron($nameCron) {
        $connection = ConnectionManager::get('default');
        $date = date('Y-m-d H:i:s',time());
        $sql = "UPDATE cron_job_report SET end = '{$date}' WHERE `name` = '{$nameCron}'";
        $connection->execute($sql,[]);
    }

    function statusCronJob($nameCron, $count) {
        $connection = ConnectionManager::get('default');
        $sql = "SELECT * FROM cron_job_report WHERE name = '{$nameCron}'";

        $currrenTime = date('H:i:s',time());


        $cronInfoResult = $connection->execute($sql,[]);
        $cronInfo       = $this->convertToAssoc($cronInfoResult)[0];

        if( $cronInfo['status'] == 1 && ( $cronInfo['from_run'] < $currrenTime && $cronInfo['to_run'] > $currrenTime ) && $count > $cronInfo['count'] ) {
            $count = $cronInfo['count'] + 1;
            $sql = "UPDATE cron_job_report SET count = {$count} WHERE name = '{$nameCron}' AND id = {$cronInfo['id']}";
            $connection->execute($sql,[]);
            return true;
        } else if($cronInfo['status'] == 2){
            Log::write('info', '--<<cron was running');
        }

        return false;
    }

    function logConfig() {
        Log::config('reportInfo', [
            'className' => 'Cake\Log\Engine\FileLog',
            'path' => ROOT. DS . 'logs/logsReports/ReportEmployee/',
            'levels' => ['warning', 'error', 'critical', 'alert', 'emergency','info'],
            'file' => 'ReportEmployeeInfo.log',
        ]);
    }

    function convertToAssoc($connResult) {
        $count = $connResult->columnCount();
        $connData = [];
        $allData = $connResult->fetchAll();
        foreach ($allData as $key=>$data) {
            for($i=0;$i<$count;$i++) {
                $rowName    = $connResult->getIterator()->getColumnMeta($i)['name'];
                $rowValue   = $data[$i];
                $connData[$key][$rowName] = $rowValue;
            }
        }
        return $connData;
    }

    function getAcademicPeriodId(){
        $connection = ConnectionManager::get('default');
        $sql = "SELECT `id` 
                FROM academic_periods 
                WHERE `current` = 1";
        return $connection->execute($sql,[])->fetch()[0];
    }

    function clearTable() {
        $connection = ConnectionManager::get('default');
        $sql = "TRUNCATE TABLE employees_report;";
        Log::write('info', 'truncate employees_report');
        $connection->execute($sql,[]);
    }

    function insertIntoReportEmployees($academicPeriodId) {
        $connection = ConnectionManager::get('default');
        $this->setStatusCron( 'ReportEmployees', 2 );
        Log::write('info', 'insert in to table employees_report reports');
        $this->clearTable();
        $query = "
    INSERT INTO employees_report
          (name,region,org_struct,area_level_id,code,staff_male_count,staff_female_count,staff_total_count,student_male_count,student_female_count,student_total_count,all_total_count,type_organization)
    SELECT
    t0.name_sruct as name,
    t0.area_parent as region,
    t0.org_name AS org_struct,
    t0.area_level_id AS area_level_id,
    t1.code AS code,
    t1.count AS staff_male_count,
    t2.count AS staff_female_count,
    ( IFNULL(t1.count,0) + IFNULL(t2.count,0)) AS staff_total_count,
    t3.count AS student_male_count,
    t4.count AS student_female_count,
    ( IFNULL(t3.count,0) + IFNULL(t4.count,0) ) AS student_total_count,
    t_all.count AS all_total_count,
    t0.type_organization
        FROM (
            SELECT institutions.name AS org_name,
            institution_type_id AS type_organization,
            area_parent.name AS area_parent,
            areas.name AS name_sruct,
            areas.area_level_id AS area_level_id
            FROM institutions 
            INNER JOIN areas
            ON areas.id = institutions.area_id
            INNER JOIN areas as area_parent
            ON areas.parent_id = area_parent.id
            GROUP BY org_name
            ) AS t0
        LEFT JOIN (
            SELECT
            areas.name AS name_sruct,
            area_parent.name AS area_parent,
            areas.area_level_id AS area_level_id,
            areas.code AS code,
            institutions.name as org_name,
            COUNT(DISTINCT security_users.id) AS count,
            security_users.gender_id,
            'staff_male' AS position_
            FROM areas
            INNER JOIN areas as area_parent
            ON areas.parent_id = area_parent.id
            INNER JOIN institutions
            ON areas.id = institutions.area_id
            INNER JOIN institution_staff
            ON institution_staff.institution_id = institutions.id
            INNER JOIN security_users
            ON security_users.id = institution_staff.staff_id
            WHERE security_users.gender_id = 1
            AND institution_staff.staff_status_id = 1
            GROUP BY org_name
        ) AS t1 
        ON t0.org_name = t1.org_name
        LEFT JOIN
        (
            SELECT areas.name AS name_sruct,
            institutions.name as org_name,
            COUNT(DISTINCT security_users.id) AS count,
            security_users.gender_id,
            'staff_female' AS position_
            FROM areas
            INNER JOIN institutions
            ON areas.id = institutions.area_id
            INNER JOIN institution_staff
            ON institution_staff.institution_id = institutions.id
            INNER JOIN security_users
            ON security_users.id = institution_staff.staff_id
            WHERE security_users.gender_id = 2
            AND institution_staff.staff_status_id = 1
            GROUP BY org_name
        ) as t2
        ON t0.org_name = t2.org_name
        LEFT JOIN
        (
            SELECT areas.name AS name_sruct,
            institutions.name as org_name,
            COUNT(DISTINCT security_users.id) AS count,
            security_users.gender_id,
            'student_male' AS position_
            FROM areas
            INNER JOIN institutions
            ON areas.id = institutions.area_id
            INNER JOIN institution_students
            ON institution_students.institution_id = institutions.id
            INNER JOIN security_users
            ON security_users.id = institution_students.student_id
            WHERE security_users.gender_id = 1
            AND academic_period_id = {$academicPeriodId}
            GROUP BY org_name
        ) AS t3 
        ON t0.org_name = t3.org_name
        LEFT JOIN
        (
            SELECT areas.name AS name_sruct,
            institutions.name as org_name,
            COUNT(DISTINCT security_users.id) AS count,
            security_users.gender_id,
            'student_female' AS position_
            FROM areas
            INNER JOIN institutions
            ON areas.id = institutions.area_id
            INNER JOIN institution_students
            ON institution_students.institution_id = institutions.id
            INNER JOIN security_users
            ON security_users.id = institution_students.student_id
            WHERE security_users.gender_id = 2
            AND academic_period_id = {$academicPeriodId}
            GROUP BY org_name
        ) AS t4 
        ON t0.org_name = t4.org_name
        LEFT JOIN (
        SELECT (IFNULL(t_s.count,0) + IFNULL(t_s2.count,0)) AS count, t_s.org_name AS org_name
            FROM (
                SELECT 	areas.name AS name_sruct,
                        institutions.name as org_name,
                        COUNT(DISTINCT security_users.id) AS count,
                        security_users.id AS user_id,
                        'student_all' AS position_
                        FROM areas
                        INNER JOIN institutions
                        ON areas.id = institutions.area_id
                        INNER JOIN institution_students
                        ON institution_students.institution_id = institutions.id
                        INNER JOIN security_users
                        ON security_users.id = institution_students.student_id
                        AND academic_period_id = {$academicPeriodId}
                        GROUP BY org_name
            ) AS t_s 
            LEFT JOIN (
                SELECT
                    institutions.name as org_name,
                    COUNT(DISTINCT security_users.id) AS count,
                    'staff_all' AS position_,
                    security_users.id AS user_id
                    FROM areas
                    INNER JOIN areas as area_parent
                    ON areas.parent_id = area_parent.id
                    INNER JOIN institutions
                    ON areas.id = institutions.area_id
                    INNER JOIN institution_staff
                    ON institution_staff.institution_id = institutions.id
                    
                    INNER JOIN security_users
                    ON security_users.id = institution_staff.staff_id
                    AND institution_staff.staff_status_id = 1 
                    GROUP BY org_name
            ) AS t_s2 
            ON t_s.org_name = t_s2.org_name
            AND t_s.user_id NOT IN (t_s2.user_id)
        ) AS t_all 
        ON t0.org_name = t_all.org_name
        ";

//        Log::write('info', 'base query '."\n".$query);
        $connection->query($query);
    }

}
