<?php
/**
 * [EduSoho API](http://developer.edusoho.com/api/)
 * Date: 2022-01-11.
 *
 * @author xuefeng <i@oiuv.cn>
 *
 * @version 2.0.0
 */

namespace Oiuv\EduSohoApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * 阔知网校API接口[EduSoho API](http://developer.edusoho.com/api/).
 */
class ES
{
    private $headers;
    private $debug;

    /**
     * HTTP 客户端.
     */
    private $client;

    /**
     * 构造方法.
     *
     * @param string $base_uri 网校API地址，格式为网校地址+/api/，如：https://wlkt.cuploeru.com/api/
     * @param int    $error    是否开启DEBUG，默认开启，开启后出错返回异常消息，关闭后只返回HTTP状态码
     *
     * @return void
     */
    public function __construct($base_uri, $error = 1)
    {
        $this->client = new Client(['base_uri' => $base_uri]);
        $this->debug = $error;
        $this->headers = [
            'Accept'        => 'application/vnd.edusoho.v2+json',
        ];
    }

    /**
     * API认证，对部分接口需要认证，使用 Basic Authentication 认证需要传用户名、密码，或使用 X-Auth-Token 认证传token.
     *
     * @param string $token    API认证token或用户名
     * @param string $password 可选参数，如果第一个参数是用户名，需要提供密码认证
     *
     * @return void
     */
    public function auth($token, $password = null)
    {
        if ($password) {
            $auth = base64_encode(trim($token).':'.trim($password));
            $this->headers = [
                'authorization' => "Basic $auth",
                'Accept'        => 'application/vnd.edusoho.v2+json',
                'cache-control' => 'no-cache',
            ];
        } else {
            $this->headers = [
                'X-Auth-Token'  => trim($token),
                'Accept'        => 'application/vnd.edusoho.v2+json',
                'cache-control' => 'no-cache',
            ];
        }
    }

    /**
     * HTTP请求客户端.
     *
     * @param string $method 请求方法posst/get
     * @param string $uri    请求API地址
     * @param array  $data   可选，请求参数内容
     *
     * @return mixed
     */
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
        } catch (RequestException $exception) {
            if ($this->debug) {
                // $error = $exception->getMessage();
                $error = $exception->getResponse()->getBody()->getContents();

                return substr($error, strpos($error, '{'));
            } else {
                return $exception->getCode();
            }
        }
    }

    /**
     * 获取access_token，需要认证
     *
     * @return string 成功响应返回json字符串，包括access token和AuthenticatedUser结构体（http://developer.edusoho.com/api/user.html#AuthenticatedUser）
     */
    public function accessToken()
    {
        $response = $this->client('POST', 'tokens');

        return $response;
    }

    //###################
    // 用户
    //###################

    /**
     * 获取当前用户信息，需要认证
     *
     * @return string 成功响应AuthenticatedUser结构体（http://developer.edusoho.com/api/user.html#AuthenticatedUser）
     */
    public function me()
    {
        $response = $this->client('GET', 'me');

        return $response;
    }

    /**
     * 我的教学计划，需要认证，按照最近查看时间排序.
     *
     * @param int|string $offset 分页偏移值, 默认0
     * @param int|string $limit  每一页数量, 默认10
     *
     * @return string 成功响应pageList<SimpleMeCourse>结构体（http://developer.edusoho.com/api/user.html）
     */
    public function myCourses($offset = 0, $limit = 10)
    {
        $data = [
            'offset' => $offset,
            'limit'  => $limit,
        ];
        $response = $this->client('GET', 'me/courses', $data);

        return $response;
    }

    /**
     * 我的直播课程，需要认证，按照最近查看时间排序.
     *
     * @return string 成功响应list<SimpleCourseSet>结构体（http://developer.edusoho.com/api/course.html#SimpleCourseSet）
     */
    public function myLiveCourseSets()
    {
        $response = $this->client('GET', 'me/live_course_sets');

        return $response;
    }

    /**
     * 我的班级，需要认证
     *
     * @return string 成功响应list<SimpleClassroom>结构体（http://developer.edusoho.com/api/classroom.html#SimpleClassroom）
     */
    public function myClassrooms()
    {
        $response = $this->client('GET', 'me/classrooms');

        return $response;
    }

    /**
     * 查询用户，不需要认证
     *
     * @param string $identifyType 标示类型 {nickname,id,email,mobile,token}
     * @param string $value        用户标示类型对应的数据，注意类型token的值是uuid
     *
     * @return string 成功响应list<SimpleUser>结构体（http://developer.edusoho.com/api/user.html#SimpleUser）
     */
    public function users($identifyType, $value)
    {
        $data = [
            'identifyType' => $identifyType,
        ];
        $response = $this->client('GET', "users/$value", $data);

        return $response;
    }

    //###################
    // 分类
    //###################

    /**
     * 获取课程或班级分类树，不需要认证
     *
     * @param int $type 0为课程分类，1为班级分类，默认值为0
     *
     * @return string 成功响应Tree<Category>结构体（http://developer.edusoho.com/api/category.html）
     */
    public function categories($type = 0)
    {
        $groupCode = $type ? 'classroom' : 'course';
        $response = $this->client('GET', "categories/$groupCode");

        return $response;
    }

    //###################
    // 课程
    //###################

    /**
     * 获取课程信息 OR 获取课程列表（搜索），不需要认证
     *
     * @param mixed $data 课程ID($data = 1)或分页数组($data = ['type' => 'normal', 'categoryId' => 'all', 'title' => '音乐', 'sort' => '-studentNum', 'offset' => 0, 'limit'  => 10];)
     *
     * @return string 成功响应pagelist<CourseSet>结构体（http://developer.edusoho.com/api/course.html#CourseSet）
     */
    public function courseSets($data = [])
    {
        if (is_array($data)) {
            $response = $this->client('GET', 'course_sets', $data);
        } else {
            $response = $this->client('GET', "course_sets/$data");
        }

        return $response;
    }

    /**
     * 获取课程所有学员（去重），不需要认证
     *
     * @param int|string $courseSetId 课程ID
     * @param int|string $offset      分页偏移值,默认0
     * @param int|string $limit       每一页数量,默认10
     *
     * @return string 成功响应list<CourseMember>结构体（http://developer.edusoho.com/api/course.html#CourseMember）
     */
    public function courseSetMembers($courseSetId, $offset = 0, $limit = 10)
    {
        $data = [
            'offset' => $offset,
            'limit'  => $limit,
        ];
        $response = $this->client('GET', "course_sets/$courseSetId/latest_members", $data);

        return $response;
    }

    /**
     * 获取单个教学计划 OR 获取教学计划列表（搜索），不需要认证
     *
     * @param mixed $data 课程计划ID($data = 1)或分页数组($data = ['type' => 'normal', 'categoryId' => 'all', 'title' => '音乐', 'sort' => '-studentNum', 'offset' => 0, 'limit'  => 10];)
     *
     * @return string 成功响应list<Course>结构体（http://developer.edusoho.com/api/course.html#Course）
     */
    public function courses($data = [])
    {
        if (is_array($data)) {
            $response = $this->client('GET', 'courses', $data);
        } else {
            $response = $this->client('GET', "courses/$data");
        }

        return $response;
    }

    /**
     * 获取课程已发布的教学计划.
     *
     * @param int|string $courseSetId 课程ID
     *
     * @return string 成功响应list<Course>结构体（http://developer.edusoho.com/api/course.html#Course）
     */
    public function courseSet($courseSetId)
    {
        $response = $this->client('GET', "course_sets/$courseSetId/courses");

        return $response;
    }

    /**
     * 获取我加入的教学计划，需要认证
     *
     * @param int|string $courseSetId 课程ID
     *
     * @return string 成功响应list<CourseMember>结构体（http://developer.edusoho.com/api/course.html#CourseMember）
     */
    public function myCourseSetMember($courseSetId)
    {
        $response = $this->client('GET', "me/course_sets/$courseSetId/course_members");

        return $response;
    }

    /**
     * 加入教学计划，成为学员（测试免费课可以，收费课不能直接加），需要认证用户.
     *
     * @param int|string $courseId 课程计划ID
     *
     * @return string 加入成功返回CourseMember（http://developer.edusoho.com/api/course.html#CourseMember），加入失败返回空对象
     */
    public function member($courseId)
    {
        $response = $this->client('POST', "courses/$courseId/members");

        return $response;
    }

    /**
     * 获取教学计划学员，不需要认证
     *
     * @param int|string $courseId 课程计划ID
     * @param array      $data     可选参数（$data = ['role' => 'student', 'offset' => 0, 'limit' => 10];）
     *
     * @return string 成功响应list<CourseMember>结构体（http://developer.edusoho.com/api/course.html#CourseMember）
     */
    public function courseMembers($courseId, $data = [])
    {
        $response = $this->client('GET', "courses/$courseId/members", $data);

        return $response;
    }

    /**
     * 获取单个教学计划成员，需要认证用户.
     *
     * @param int|string $courseId 课程计划ID
     *
     * @return string 成功响应CourseMember结构体（http://developer.edusoho.com/api/course.html#CourseMember）
     */
    public function myCourseMember($courseId)
    {
        $response = $this->client('GET', "me/course_members/$courseId");

        return $response;
    }

    /**
     * 获取单个任务，需要认证用户.
     *
     * @param int|string $courseId 课程计划ID
     * @param int|string $taskId   课程计划任务ID
     *
     * @return string 成功响应CourseTask结构体（http://developer.edusoho.com/api/course.html#CourseTask）
     */
    public function courseTask($courseId, $taskId)
    {
        $response = $this->client('GET', "courses/$courseId/tasks/$taskId");

        return $response;
    }

    /**
     * 获取教学计划的目录列表，不需要认证
     *
     * @param int|string $courseId      课程计划ID
     * @param int|string $onlyPublished onlyPublished=1，过滤掉未发布的任务
     *
     * @return string 成功响应CourseItem结构体（http://developer.edusoho.com/api/course.html#CourseItem）
     */
    public function courseItems($courseId, $onlyPublished = 1)
    {
        $data = [
            'onlyPublished' => $onlyPublished,
        ];
        $response = $this->client('GET', "courses/$courseId/items", $data);

        return $response;
    }

    /**
     * 获取计划下的第一个试看任务，不需要用户认证
     *
     * @param int|string $courseId 课程计划ID
     *
     * @return string 成功响应CourseTask（http://developer.edusoho.com/api/course.html#CourseTask）
     */
    public function firstTrialTask($courseId)
    {
        $response = $this->client('GET', "courses/$courseId/trial_tasks/first");

        return $response;
    }

    /**
     * 获取计划的所有评价，不需要用户认证
     *
     * @param int|string $courseId 课程计划ID
     * @param int|string $offset   分页偏移值,默认0
     * @param int|string $limit    每一页数量,默认10
     *
     * @return string 成功响应list<CourseReview>结构体（http://developer.edusoho.com/api/course.html#CourseReview）
     */
    public function courseReviews($courseId, $offset = 0, $limit = 10)
    {
        $data = [
            'offset' => $offset,
            'limit'  => $limit,
        ];
        $response = $this->client('GET', "courses/$courseId/reviews", $data);

        return $response;
    }

    /**
     * 获取课程的所有评价，不需要用户认证
     *
     * @param int|string $courseSetId 课程ID
     * @param int|string $offset      分页偏移值,默认0
     * @param int|string $limit       每一页数量,默认10
     *
     * @return string 成功响应list<CourseReview>结构体（http://developer.edusoho.com/api/course.html#CourseReview）
     */
    public function courseSetReviews($courseSetId, $offset = 0, $limit = 10)
    {
        $data = [
            'offset' => $offset,
            'limit'  => $limit,
        ];
        $response = $this->client('GET', "course_sets/$courseSetId/reviews", $data);

        return $response;
    }

    /**
     * 获取我的教学计划下的学习进度，需要认证用户.
     *
     * @param int|string $courseId 课程计划ID
     *
     * @return string
     */
    public function myCourseLearningProgress($courseId)
    {
        $response = $this->client('GET', "me/course_learning_progress/$courseId");

        return $response;
    }

    /**
     * 我收藏的课程，需要认证用户.
     *
     * @param mixed $data 课程ID($data = 1)或分页数组($data = ['offset' => 0, 'limit'  => 10];)
     *
     * @return string 成功响应pageList<SimpleCourseSet>（http://developer.edusoho.com/api/course.html#SimpleCourseSet）
     */
    public function myFavoriteCourseSets($data = [])
    {
        if (is_array($data)) {
            $response = $this->client('GET', 'me/favorite_course_sets', $data);
        } else {
            $response = $this->client('GET', "me/favorite_course_sets/$data");
        }

        return $response;
    }

    /**
     * 收藏课程，需要认证用户.
     *
     * @param int|string $courseSetId 课程ID
     *
     * @return string success
     */
    public function favoriteCourseSet($courseSetId)
    {
        $data = [
            'courseSetId' => $courseSetId,
        ];
        $response = $this->client('POST', 'me/favorite_course_sets', $data);

        return $response;
    }

    /**
     * 取消收藏课程，需要认证用户.
     *
     * @param int|string $courseSetId 课程ID
     *
     * @return string success
     */
    public function delFavoriteCourseSet($courseSetId)
    {
        $response = $this->client('DELETE', "me/favorite_course_sets/$courseSetId");

        return $response;
    }

    /**
     * 退出计划，需要认证用户.
     *
     * @param int|string $courseId 课程计划ID
     * @param string     $reason   退出原因
     *
     * @return string success   是否成功
     */
    public function exitCourse($courseId, $reason = '')
    {
        $data = [
            'reason' => $reason,
        ];
        $response = $this->client('DELETE', "me/course_members/$courseId", $data);

        return $response;
    }

    /**
     * 学习任务，需要认证用户.
     *
     * @param int|string $courseId 课程计划ID
     * @param int|string $taskId   课程计划任务ID
     * @param string     $event    任务结果:doing,finish
     * @param int        $lastTime 最近一次记录任务的时间戳
     *
     * @return string
     */
    public function taskEvent($courseId, $taskId, $event, $lastTime)
    {
        $data = [
            'lastTime' => $lastTime,
        ];
        $response = $this->client('PATCH', "courses/$courseId/tasks/$taskId/events/$event", $data);

        return $response;
    }

    /**
     * 查看计划，不需要认证
     *
     * @param int|string $courseId 课程计划ID
     *
     * @return string
     */
    public function courseView($courseId)
    {
        $response = $this->client('PATCH', "courses/$courseId/events/course_view");

        return $response;
    }

    //###################
    // 班级
    //###################

    /**
     * 获取班级信息，不需要认证
     *
     * @param int|array $data 班级ID|班级查询参数数组（$data = ['categoryId' => 'all', 'title' => '音乐', 'sort' => '-studentNum', 'offset' => 0, 'limit'  => 10];）
     *
     * @return string 成功响应Classroom结构体（http://developer.edusoho.com/api/classroom.html#Classroom）
     */
    public function classrooms($data = [])
    {
        if (is_array($data)) {
            $response = $this->client('GET', 'classrooms', $data);
        } else {
            $response = $this->client('GET', "classrooms/$data");
        }

        return $response;
    }

    /**
     * 班级成员（查看自己是否是指定班级的成员），需要认证
     *
     * @param int $classroomId 班级ID
     *
     * @return string 成功响应ClassroomMember结构体
     */
    public function myClassroomMember($classroomId)
    {
        $response = $this->client('GET', "me/classroom_members/$classroomId");

        return $response;
    }

    /**
     * 获取班级计划，不需要认证
     *
     * @param int $classroomId 班级ID
     *
     * @return string 成功响应list<Course>结构体（http://developer.edusoho.com/api/course.html#Course）
     */
    public function classroomCourses($classroomId)
    {
        $response = $this->client('GET', "classrooms/$classroomId/courses");

        return $response;
    }

    /**
     * 加入班级，需要认证
     *
     * @param int $classroomId 班级ID
     *
     * @return string 成功响应ClassroomMember结构体
     */
    public function classroomMember($classroomId)
    {
        $response = $this->client('POST', "classrooms/$classroomId/members");

        return $response;
    }

    /**
     * todo 营销平台加入班级.
     *
     * @param int $classroomId 班级ID
     *
     * @return string 成功响应ClassroomMember结构体
     */
    public function classroomMarketingMember($classroomId)
    {
        $response = $this->client('POST', "classrooms/$classroomId/marketing_members");

        return $response;
    }

    //###################
    // 题库练习
    //###################

    /**
     * 获取题库练习详情，不认证用户.
     *
     * @param int $id 题库练习ID
     *
     * @return string 成功响应ItemBankExercise结构体（http://developer.edusoho.com/api/item-bank-exercise.html#ItemBankExercise）
     */
    public function itemBankExercises($id)
    {
        $response = $this->client('GET', "item_bank_exercises/$id");

        return $response;
    }

    /**
     * 获取题库下的学员，不认证用户.
     *
     * @param int $id 题库练习ID
     *
     * @return string 成功响应MemberList结构体（http://developer.edusoho.com/api/item-bank-exercise.html#MemberList）
     */
    public function itemBankExercisesMembers($id)
    {
        $response = $this->client('GET', "item_bank_exercises/$id/members");

        return $response;
    }

    /**
     * 获取模块列表，不认证用户.
     *
     * @param int $id 题库练习ID
     *
     * @return string 成功响应ModuleList结构体（http://developer.edusoho.com/api/item-bank-exercise.html#ModuleList）
     */
    public function itemBankExercisesModules($id)
    {
        $response = $this->client('GET', "item_bank_exercises/$id/modules");

        return $response;
    }

    //###################
    // 商品
    //###################

    /**
     * 获取商品，不认证用户.
     *
     * @param int $id 商品ID
     *
     * @return string 成功响应GoodsInfo结构体（http://developer.edusoho.com/api/goods.html#GoodsInfo）
     */
    public function goods($id)
    {
        $response = $this->client('GET', "goods/$id");

        return $response;
    }

    /**
     * 获取商品推荐内容，不认证用户.
     *
     * @param int $id 商品ID
     * @param $componment 商品推荐内容
     *
     * @return string 成功响应Component结构体（http://developer.edusoho.com/api/goods.html#Component）
     */
    public function goodsComponents($id, $component)
    {
        $response = $this->client('GET', "goods/$id/components/{$component}");

        return $response;
    }

    //###################
    // 证书
    //###################

    /**
     * 获取获取证书列表或证书详情，不认证用户.
     *
     * @param int $id 证书ID
     *
     * @return string 成功响应Certificate结构体（http://developer.edusoho.com/api/certificate.html#Certificate）
     */
    public function certificates($id = null)
    {
        if ($id) {
            $response = $this->client('GET', "certificates/{$id}");
        } else {
            $response = $this->client('GET', 'certificates');
        }

        return $response;
    }

    //###################
    // 用户信息采集
    //###################

    /**
     * 根据事件id获取表单，需认证
     *
     * @param int $id 事件ID
     *
     * @return string 成功响应InformationCollectForm结构体（http://developer.edusoho.com/api/information-collect.html#InformationCollectForm）
     */
    public function informationCollectForm($eventId)
    {
        $response = $this->client('GET', "information_collect_form/{$eventId}");

        return $response;
    }

    /**
     * 根据动作获取事件，需认证
     *
     * @param int    $type     动作事件：0：buy_after=购买后，1：buy_before=购买前
     * @param string $target   目标类型：0：classroom，1：course，2：none
     * @param int    $targetId 目标ID 0为当前类型全部
     *
     * @return string 成功响应InformationCollectEvent结构体（http://developer.edusoho.com/api/information-collect.html#InformationCollectEvent）
     */
    public function informationCollectEvent($type = 0, $target = 0, $targetId = 0)
    {
        $action = $type ? 'buy_before' : 'buy_after';
        $targetType = ['classroom', 'course', 'none'];
        $data = [
            'targetType' => $targetType[$target],
            'targetId'   => $targetId,
        ];
        $response = $this->client('GET', "information_collect_event/{$action}", $data);

        return $response;
    }

    //###################
    // 账户
    //###################

    /**
     * 获得我的虚拟币账户，需认证用户.
     *
     * @return string 成功响应CashAccount结构体（http://developer.edusoho.com/api/account.html）
     */
    public function myCashAccount()
    {
        $response = $this->client('GET', 'me/cash_account');

        return $response;
    }

    //###################
    // 订单
    //###################

    /**
     * 确认订单信息，需要认证用户.
     *
     * @param int|string $targetId 商品ID(班级或课程ID)
     * @param string     $type     商品类型:0为classroom，1为course
     *
     * @return string 成功响应OrderInfo结构体（http://developer.edusoho.com/api/order.html）
     */
    public function orderInfo($targetId, $type = 0)
    {
        $targetType = ['classroom', 'course'];
        $data = [
            'targetType' => $targetType[$type],
            'targetId'   => $targetId,
        ];
        $response = $this->client('POST', 'order_info', $data);

        return $response;
    }

    /**
     * 创建订单(返回的订单编号不对?)，需要认证用户.
     *
     * @param int|string $targetId 商品ID（班级、课程或VIP会员ID）
     * @param int|string $type     商品类型:0为classroom，1为course，2为vip
     *
     * @return string
     */
    public function orders($targetId, $type = 0, $couponCode = null)
    {
        $targetType = ['classroom', 'course', 'vip'];
        $data = [
            'targetType' => $targetType[$type],
            'targetId'   => $targetId,
            'couponCode' => $couponCode,
        ];
        $response = $this->client('POST', 'orders', $data);

        return $response;
    }

    /**
     * 支付(弃用)，需要用户认证
     *
     * @param int $orderId
     * @param int $type
     * @param int $payment
     *
     * @return string
     */
    public function payCenter($orderId, $type = 0, $payment = 0)
    {
        $targetType = ['classroom', 'course', 'vip'];
        $payType = ['wechat', 'alipay', 'coin'];
        $data = [
            'targetType' => $targetType[$type],
            'orderId'    => $orderId,
            'payment'    => $payType[$payment],
        ];
        $response = $this->client('POST', 'pay_center', $data);

        return $response;
    }

    /**
     * 新的支付接口(推荐)，需要认证用户.
     *
     * @param int $orderSn 订单号
     * @param int $type    支付方式：0：微信扫码支付，1：支付包 Web 支付，2：支付宝 Wap 支付，3：微信 Wap 支付，4：微信内支付
     *
     * @return string 成功响应Trade结构体（http://developer.edusoho.com/api/order.html）
     */
    public function trades($orderSn, $type = 0)
    {
        $gateway = ['WechatPay_Native', 'Alipay_LegacyExpress', 'Alipay_LegacyWap', 'WechatPay_MWeb', 'WechatPay_Js', 'Coin', 'Lianlian_Web', 'Lianlian_Wap'];
        $data = [
            'orderSn' => $orderSn,
            'gateway' => $gateway[$type],
            'type'    => 'purchase',
        ];
        $response = $this->client('POST', 'trades', $data);

        return $response;
    }

    //###################
    // 优惠码
    //###################

    /**
     * 获取当前用户的优惠码（需要认证）.
     *
     * @return string 成功响应list<Coupon>结构体(http://developer.edusoho.com/api/coupon.html#Coupon)
     */
    public function coupons()
    {
        $response = $this->client('GET', 'me/coupons');

        return $response;
    }

    /**
     * todo 营销平台创建优惠码
     */

    //###################
    // 移动端API
    //###################

    /**
     * 获取移动端频道，不需要认证
     *
     * @return string 成功响应list<AppChannel>(http://developer.edusoho.com/api/app.html)
     */
    public function appChannels()
    {
        $response = $this->client('GET', 'app/channels');

        return $response;
    }

    //###################
    // 网站后台设置
    //###################

    /**
     * 查看网站某个功能的设置，不需要认证用户.
     *
     * @param string $type 可用类型:site/wap/register/payment/vip/magic/ugc/ugc_review/ugc_note/ugc_thread
     *
     * @return string http://developer.edusoho.com/api/setting.html
     */
    public function settings($type)
    {
        $response = $this->client('GET', "settings/{$type}");

        return $response;
    }

    //###################
    // 验证码
    //###################

    /**
     * 获取图形验证码，不需要认证
     *
     * @return string http://developer.edusoho.com/api/captcha.html
     */
    public function captcha()
    {
        $response = $this->client('POST', 'captcha');

        return $response;
    }

    /**
     * 验证图形验证码，不需要认证
     *
     * @param string $captcha      待验证的字符串
     * @param string $captchaToken 验证码的令牌
     *
     * @return string 返回验证结果:  success：正确 expired：已过期或者达到验证次数 invalid：错误
     */
    public function verifyCaptcha($captcha, $captchaToken)
    {
        $data = [
            'phrase' => $captcha,
        ];
        $response = $this->client('GET', "captcha/{$captchaToken}", $data);

        return $response;
    }

    /**
     * 获取短信验证码，不需要认证
     *
     * @param int|string $mobile 手机号码
     *
     * @return string
     */
    public function smsCaptcha($mobile, $phrase)
    {
        $data = [
            'type'         => 'register',
            'mobile'       => $mobile,
            'phrase'       => $phrase,
            'captchaToken' => $mobile.$phrase,
        ];
        $response = $this->client('POST', 'sms_center', $data);

        return $response;
    }

    //###################
    // 通知
    //###################

    /**
     * 获取通知列表，需要认证
     *
     * @param array $data 可选参数见文档:http://developer.edusoho.com/api/notification.html
     *
     * @return string
     */
    public function notifications($type = 'course', $startTime = 0, $offset = 0, $limit = 10)
    {
        $data = [
            'type'      => $type,
            'startTime' => $startTime,
            'offset'    => $offset,
            'limit'     => $limit,
        ];
        $response = $this->client('GET', 'notifications', $data);

        return $response;
    }

    //###################
    // 公告
    //###################

    /**
     * 获取公告列表，不需要认证
     *
     * @param int        $startTime 起始时间timestamp（默认0，即全部）
     * @param int|string $offset    分页偏移值, 默认0
     * @param int|string $limit     每一页数量, 默认10
     *
     * @return string http://developer.edusoho.com/api/announcement.html
     */
    public function announcements($startTime = 0, $offset = 0, $limit = 10)
    {
        $data = [
            'startTime' => $startTime,
            'offset'    => $offset,
            'limit'     => $limit,
        ];
        $response = $this->client('GET', 'announcements', $data);

        return $response;
    }

    //###################
    // 资讯
    //###################

    /**
     * 获取资讯栏目，不需要认证
     *
     * @return string http://developer.edusoho.com/api/article.html#category
     */
    public function articleCategories()
    {
        $response = $this->client('GET', 'article_categories');

        return $response;
    }

    /**
     * 获取资讯列表信息.
     *
     * @return string http://developer.edusoho.com/api/article.html#articles
     */
    public function articles()
    {
        $response = $this->client('GET', 'articles');

        return $response;
    }

    /**
     * 获取资讯信息.
     *
     * @param int $id 文章ID
     *
     * @return string
     */
    public function article($id)
    {
        $response = $this->client('GET', "articles/{$id}");

        return $response;
    }

    //###################
    // 会员
    //###################

    /**
     * todo 会员.
     */

    //###################
    // 打折活动
    //###################

    /**
     * todo 打折活动.
     */

    //###################
    // 积分
    //###################

    /**
     * 我的积分.
     */
    public function myRewardPoint()
    {
        $response = $this->client('GET', 'plugins/reward_point/me/reward_point');

        return $response;
    }
}
