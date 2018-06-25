<h1 align="center">EduSoho API</h1>

## 环境需求

- PHP >= 5.6

## 安装

```shell

$ composer require oiuv/edusoho-api

```

## 使用

```php
use Oiuv\EduSohoApi\ES;

$es = new ES('API网址', 'token', '用户名', '密码');

//网校API接口部分无需认证用户可调用，部分需要认证才能调用
//无需认证,只传API地址,API地址为网校域名+"/api/"
$es = new ES('https://edusoho.test/api/');

//X-Auth-Token认证
$es = new ES('https://edusoho.test/api/', '3mathrcgw2yog04s4c8wk8ooc84k4co');

//HTTP基础认证
$es = new ES('https://edusoho.test/api/', '', '用户名', '密码');

//获取access_token，需要认证
echo $es->accessToken();
```

### 用户

> 获取当前用户信息（需要认证）
```php
echo $es->me();
```

> 我的教学计划（需要认证）
```php
echo $es->myCourses();
```

> 我的直播课程（需要认证）
```php
echo $es->myLiveCourseSets();
```

> 我的班级（需要认证）
```php
echo $es->myClassrooms();
```

> 查询用户
```php
//根据nickname查询用户
echo $es->users('nickname', $nickname);

//根据id查询用户
echo $es->users('id', $id);

//根据email查询用户
echo $es->users('email', $email);

//根据mobile查询用户
echo $es->users('mobile', $mobile);

//根据token查询用户
echo $es->users('token', $token);
```

### 课程

网校课程是CourseSet，课程计划是Course，一个课程可以有多个教学计划

> 获取课程信息
```php
//获取课程列表
echo $es->courseSets();

//按条件获取课程列表
$data = [
    'type'   => 'normal', //教学计划类型:normal,live
    'title'  => '音乐', //课程标题
    'sort'   => '-studentNum', //排序方式'createdTime','updatedTime','recommendedSeq','hitNum','recommendedTime','rating','studentNum'，-field代表倒序
    'offset' => 0,
    'limit'  => 100
];
echo $es->courseSets($data);

//获取指定课程信息
echo $es->courseSets($courseSetId);
```

> 获取课程所有学员（去重）
```php
//根据课程ID获取课程所有教学计划学员信息
echo $es->courseSetMembers($courseSetId);
```

> 教学计划信息
```php
//根据课程计划ID获取单个教学计划信息
echo $es->courses($courseId);

//获取教学计划列表
echo $es->courses();

//按条件获取教学计划列表
$data = [
    'type'   => 'live', //教学计划类型:normal,live
    'title'  => '音乐', //课程标题
    'sort'   => '-studentNum', //排序方式'createdTime','updatedTime','recommendedSeq','hitNum','recommendedTime','rating','studentNum'，-field代表倒序
    'offset' => 0,
    'limit'  => 100
];
echo $es->courses($data);

//根据课程ID获取课程已发布的教学计划信息
echo $es->courseSet($courseSetId);
```

> 教学计划成员信息
```php
//加入教学计划，成为学员(好像只可以加免费课)
echo $es->member($courseId);

//根据课程计划ID获取教学计划学员学习信息
echo $es->courseMembers($courseId);

//根据课程ID获取认证用户的课程计划学习信息(需要认证)
echo $es->myCourseSetMember($courseSetId);

//根据课程计划ID获取认证用户的课程计划学习信息(需要认证)
echo $es->myCourseMember($courseId);
```

> 教学计划任务
```php
//获取单个任务
echo $es->courseTask($courseId, $taskId);

//获取教学计划的目录列表
 echo $es->courseItems($courseId);

//获取计划下的第一个试看任务
echo $es->firstTrialTask($courseId);
```

> 评价
```php
//获取计划的所有评价
echo $es->courseReviews($courseId);

//获取课程的所有评价
echo $es->courseSetReviews($courseSetId);

```

> 学习进度
```php
//获取我的教学计划下的学习进度
echo $es->myCourseLearningProgress($courseId);
```

> 课程收藏
```php
//我收藏的课程
echo $es->myFavoriteCourseSets();

//是否收藏课程
echo $es->myFavoriteCourseSets($courseSetId);

//收藏课程
echo $es->favoriteCourseSet($courseSetId);

//取消收藏课程
echo $es->delFavoriteCourseSet($courseSetId);
```

> 退出
```php
//退出计划
echo $es->exitCourse($courseId, $reason = '');

```

> 事件
```php
//学习任务{envet:doing,finish}
echo $es->taskEvent($courseId, $taskId, $event, $lastTime);
```

### 班级
```php
//根据班级ID获取班级信息
echo $es->classrooms($classroomId);

//获取班级列表
echo $es->classrooms();

//我的班级个人学习资料(需要认证)
echo $es->myClassroomMember($classroomId);

//获取班级计划
echo $es->classroomCourses($classroomId);

//加入班级(需要认证)
echo $es->classroomMember($classroomId);
```

### 移动端API
> 获取移动端频道
```php
echo $es->appChannels();
```

### 通知
> 获取通知列表(需要认证)
```php
echo $es->notifications();
```

### 公告
> 获取公告列表
```php
echo $es->announcements();
```

> 更多方法看源码并参考[EduSoho REST API](http://developer.edusoho.com/api/)

## License

MIT
