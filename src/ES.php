<?php
/**
 * [EduSoho API](http://developer.edusoho.com/api/)
 * Date: 2018-06-23
 * @author xuefeng <i@oiuv.cn>
 * @version 1.0.1
 */

namespace Oiuv\EduSohoApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ES
{

    private $headers;
    private $debug;

    /**
     * HTTP 客户端
     */
    private $client;

    public function __construct($base_uri, $token = '', $username = '', $password = '', $debug = 0)
    {
        $this->client = new Client(['base_uri' => $base_uri]);
        $this->debug = $debug;
        if ($token) {
            $this->headers = [
                'X-Auth-Token'  => trim($token),
                'Accept'        => 'application/vnd.edusoho.v2+json',
                'cache-control' => 'no-cache'
            ];
        } elseif ($username && $password) {
            $auth = base64_encode(trim($username) . ":" . trim($password));
            $this->headers = [
                'authorization' => "Basic $auth",
                'Accept'        => 'application/vnd.edusoho.v2+json',
                'cache-control' => 'no-cache'
            ];
        } else
            $this->headers = [
                'Accept'        => 'application/vnd.edusoho.v2+json',
                'cache-control' => 'no-cache'
            ];
    }

    public function client($method, $uri, $data = [])
    {
        //var_dump($this->headers);
        if (!strcasecmp($method, 'GET')) {
            $action = 'query';
        } else {
            $action = 'json';
        }
        try {
            $response = $this->client->request($method, $uri, [
                'headers' => $this->headers,
                $action   => $data,
            ]);
            return $response->getBody()->getContents();
        } catch (GuzzleException $exception) {
            if ($this->debug)
                return $exception->getMessage();
            else
                return $exception->getCode();
        }
    }

    /**
     * 获取access_token
     * @return String
     */
    public function accessToken()
    {
        $response = $this->client('POST', 'tokens');
        return $response;
    }

    /**
     * 获取当前用户信息
     */
    public function me()
    {
        $response = $this->client('GET', 'me');
        return $response;
    }

    /**
     * 我的教学计划
     * @param int|string $offset 分页偏移值, 默认0
     * @param int|string $limit 每一页数量, 默认10
     * @return string
     */
    public function myCourses($offset = 0, $limit = 10)
    {
        $data = [
            'offset' => $offset,
            'limit'  => $limit
        ];
        $response = $this->client('GET', 'me/courses', $data);
        return $response;
    }

    /**
     * 我的直播课程
     */
    public function myLiveCourseSets()
    {
        $response = $this->client('GET', 'me/live_course_sets');
        return $response;
    }

    /**
     * 我的班级
     */
    public function myClassrooms()
    {
        $response = $this->client('GET', 'me/classrooms');
        return $response;
    }

    /**
     * todo 查询用户
     */
    public function users($identifyType, $data)
    {
        $data = [
            'identifyType' => $data
        ];
        $response = $this->client('GET', "users/$identifyType", $data);
        return $response;
    }

    /**
     * todo 获取分类树
     */
    public function categories($groupCode)
    {
        $response = $this->client('GET', "categories/$groupCode");
        return $response;
    }

    /**
     * 获取课程信息 OR 获取课程列表
     * @link http://developer.edusoho.com/api/course.html
     * @param mixed $data 课程ID($data = 1)或分页数组($data = ['title' => '音乐', 'sort' => '-studentNum', 'offset' => 0, 'limit'  => 10];)
     * @return string
     */
    public function courseSets($data = [])
    {
        if (is_array($data))
            $response = $this->client('GET', 'course_sets', $data);
        else
            $response = $this->client('GET', "course_sets/$data");
        return $response;
    }

    /**
     * 获取课程所有学员
     * @param int|string $courseSetId 课程ID
     * @param int|string $offset 分页偏移值,默认0
     * @param int|string $limit 每一页数量,默认10
     * @return string
     */
    public function courseSetMembers($courseSetId, $offset = 0, $limit = 10)
    {
        $data = [
            'offset' => $offset,
            'limit'  => $limit
        ];
        $response = $this->client('GET', "course_sets/$courseSetId/latest_members", $data);
        return $response;
    }

    /**
     * 获取单个教学计划 OR 获取教学计划列表（搜索）
     * @param mixed $data 课程计划ID($data = 1)或分页数组($data = ['offset' => 0, 'limit'  => 10];)
     * @return string
     */
    public function courses($data = [])
    {
        if (is_array($data))
            $response = $this->client('GET', "courses", $data);
        else
            $response = $this->client('GET', "courses/$data");
        return $response;
    }

    /**
     * 获取课程已发布的教学计划
     * @param int|string $courseSetId 课程ID
     * @return string
     */
    public function courseSet($courseSetId)
    {
        $response = $this->client('GET', "course_sets/$courseSetId/courses");
        return $response;

    }

    /**
     * 获取我加入的教学计划
     * @param int|string $courseSetId 课程ID
     * @return string
     */
    public function myCourseSetMember($courseSetId)
    {
        $response = $this->client('GET', "me/course_sets/$courseSetId/course_members");
        return $response;
    }

    /**
     * todo 加入教学计划，成为学员
     * @param int|string $courseId 课程计划ID
     * @return string
     */
    public function member($courseId)
    {
        $response = $this->client('POST', "courses/$courseId/members");
        return $response;
    }

    /**
     * 获取教学计划学员
     * @param int|string $courseId 课程计划ID
     * @param array $data
     * @return string
     */
    public function courseMembers($courseId, $data = [])
    {
        $response = $this->client('GET', "courses/$courseId/members", $data);
        return $response;
    }

    /**
     * 获取单个教学计划成员
     * @param int|string $courseId 课程计划ID
     * @return string
     */
    public function myCourseMember($courseId)
    {
        $response = $this->client('GET', "me/course_members/$courseId");
        return $response;
    }

    /**
     * 获取单个任务
     * @param int|string $courseId 课程计划ID
     * @param int|string $taskId 课程计划任务ID
     * @return string
     */
    public function courseTask($courseId, $taskId)
    {
        $response = $this->client('GET', "courses/$courseId/tasks/$taskId");
        return $response;
    }

    /**
     * 获取教学计划的目录列表
     * @param int|string $courseId 课程计划ID
     * @param int|string $onlyPublished onlyPublished=1，过滤掉未发布的任务
     * @return string
     */
    public function courseItems($courseId, $onlyPublished = 1)
    {
        $data = [
            'onlyPublished' => $onlyPublished
        ];
        $response = $this->client('GET', "courses/$courseId/items", $data);
        return $response;
    }

    /**
     * 获取计划下的第一个试看任务
     * @param int|string $courseId 课程计划ID
     * @return string
     */
    public function firstTrialTask($courseId)
    {
        $response = $this->client('GET', "courses/$courseId/trial_tasks/first");
        return $response;
    }

    /**
     * 获取计划的所有评价
     * @param int|string $courseId 课程计划ID
     * @param int|string $offset 分页偏移值,默认0
     * @param int|string $limit 每一页数量,默认10
     * @return string
     */
    public function courseReviews($courseId, $offset = 0, $limit = 10)
    {
        $data = [
            'offset' => $offset,
            'limit'  => $limit
        ];
        $response = $this->client('GET', "courses/$courseId/reviews", $data);
        return $response;
    }

    /**
     * 获取课程的所有评价
     * @param int|string $courseSetId 课程ID
     * @param int|string $offset 分页偏移值,默认0
     * @param int|string $limit 每一页数量,默认10
     * @return string
     */
    public function courseSetReviews($courseSetId, $offset = 0, $limit = 10)
    {
        $data = [
            'offset' => $offset,
            'limit'  => $limit
        ];
        $response = $this->client('GET', "course_sets/$courseSetId/reviews", $data);
        return $response;
    }

    /**
     * 获取我的教学计划下的学习进度
     * @param int|string $courseId 课程计划ID
     * @return string
     */
    public function myCourseLearningProgress($courseId)
    {
        $response = $this->client('GET', "me/course_learning_progress/$courseId");
        return $response;
    }

    /**
     * 我收藏的课程
     * @param mixed $data 课程ID($data = 1)或分页数组($data = ['offset' => 0, 'limit'  => 10];)
     * @return string
     */
    public function myFavoriteCourseSets($data = [])
    {
        if (is_array($data))
            $response = $this->client('GET', "me/favorite_course_sets", $data);
        else
            $response = $this->client('GET', "me/favorite_course_sets/$data");
        return $response;
    }

    /**
     * 收藏课程
     * @param int|string $courseSetId 课程ID
     * @return string
     */
    public function favoriteCourseSet($courseSetId)
    {
        $data = [
            'courseSetId' => $courseSetId
        ];
        $response = $this->client('POST', "me/favorite_course_sets", $data);
        return $response;
    }

    /**
     * 取消收藏课程
     * @param int|string $courseSetId 课程ID
     * @return string
     */
    public function delFavoriteCourseSet($courseSetId)
    {
        $response = $this->client('DELETE', "me/favorite_course_sets/$courseSetId");
        return $response;
    }

    /**
     * 退出计划
     * @param int|string $courseId 课程计划ID
     * @param string $reason 退出原因
     * @return string
     */
    public function exitCourse($courseId, $reason = '')
    {
        $data = [
            'reason' => $reason
        ];
        $response = $this->client('DELETE', "me/course_members/$courseId", $data);
        return $response;
    }

    /**
     * 学习任务
     * @param int|string $courseId 课程计划ID
     * @param int|string $taskId 课程计划任务ID
     * @param string $event 任务结果:doing,finish
     * @param int $lastTime 最近一次记录任务的时间戳
     * @return string
     */
    public function taskEvent($courseId, $taskId, $event, $lastTime)
    {
        $data = [
            'lastTime' => $lastTime
        ];
        $response = $this->client('PATCH', "courses/$courseId/tasks/$taskId/events/$event", $data);
        return $response;
    }

    /**
     * 查看计划
     * @param int|string $courseId 课程计划ID
     * @return string
     */
    public function courseView($courseId)
    {
        $response = $this->client('PATCH', "courses/$courseId/events/course_view");
        return $response;
    }

    /**
     * 获取班级信息
     * @param int|array $data 班级ID|班级查询参数数组
     * @return string
     */
    public function classroom($data = [])
    {
        if (is_array($data))
            $response = $this->client('GET', "classrooms", $data);
        else
            $response = $this->client('GET', "classrooms/$data");
        return $response;
    }

    /**
     * 班级成员:查看自己是否是指定班级的成员
     * @param int $classroomId 班级ID
     * @return string
     */
    public function myClassroomMember($classroomId)
    {
        $response = $this->client('GET', "me/classroom_members/$classroomId");
        return $response;
    }

    /**
     * 获取班级计划
     * @param int $classroomId 班级ID
     * @return string
     */
    public function classroomCourses($classroomId)
    {
        $response = $this->client('GET', "classrooms/$classroomId/courses");
        return $response;
    }

    /**
     * todo 加入班级
     * @param int $classroomId 班级ID
     * @return string
     */
    public function classroomMember($classroomId)
    {
        $response = $this->client('POST', "classrooms/$classroomId/members");
        return $response;
    }

    /**
    * todo 营销平台加入班级
    * @param int $classroomId 班级ID
    * @return string
    */
    public function classroomMarketingMember($classroomId)
    {
        $response = $this->client('POST', "classrooms/$classroomId/marketing_members");
        return $response;
    }

    /**
     * 获得我的虚拟币账户
     */
    public function myCashAccount()
    {
        $response = $this->client('GET', "me/cash_account");
        return $response;
    }

    /**
     * todo 订单
     */

    /**
     * todo 优惠码
     */

    /**
     * 获取移动端频道
     */
    public function appChannels()
    {
        $response = $this->client('GET', "app/channels");
        return $response;
    }

    /**
     * todo 网站后台设置
     */

    /**
     * todo 验证码
     */

    /**
     * 获取通知列表
     * @param array $data
     * @return string
     */
    public function notifications($data = [])
    {
        $response = $this->client('GET', "notifications", $data);
        return $response;
    }

    /**
     * 获取公告列表
     * @param int $startTime 起始时间timestamp（默认0，即全部）
     * @param int|string $offset 分页偏移值, 默认0
     * @param int|string $limit 每一页数量, 默认10
     * @return string
     */
    public function announcements($startTime = 0, $offset = 0, $limit = 10)
    {
        $data = [
            'startTime' => $startTime,
            'offset'    => $offset,
            'limit'     => $limit
        ];
        $response = $this->client('GET', "announcements", $data);
        return $response;
    }

    /**
     * todo 获取资讯列表信息
     */

    /**
     * todo 会员
     */

    /**
     * todo 打折活动
     */

    /**
     * 我的积分
     */
    public function myRewardPoint()
    {
        $response = $this->client('GET', "plugins/reward_point/me/reward_point");
        return $response;
    }
}