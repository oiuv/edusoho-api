<h1 align="center">EduSoho API</h1>

![EduSoho](https://v.jkw.ltd/themes/jianmo/img/banner_net.jpg)

阔知网校API，方便在不改动网校源码的情况下实现网校功能二次开发，适用于所有使用阔知网校系统的网校平台。

## 环境需求

- PHP >= 5.6

## 安装

```shell
composer require oiuv/edusoho-api
```

## 使用

```php
require __DIR__ . '/vendor/autoload.php';

use Oiuv\EduSohoApi\ES;

//网校API地址为网校域名+"/api/"，如：https://edusoho.test/api/
$es = new ES('https://edusoho.test/api/');

// 部分接口无需认证用户可调用，部分接口需要认证用户才能调用，认证有二种方式：token或账号密码
// 注意：token有有效期限制，需要定期更新，token存在数据库`user_token`表中

//X-Auth-Token认证
$es->auth('token0cgw2yog04s4c8wk8ooc84k4co');

//HTTP基础认证
$es->auth('用户名', '密码');

//更新access_token，需要认证
echo $es->accessToken();

//获取班级列表，无需认证
echo $es->classrooms();
```

### 用户

```php
// 获取当前用户信息（需要认证）
echo $es->me();

// 我的教学计划（需要认证）
echo $es->myCourses();

// 我的直播课程（需要认证）
echo $es->myLiveCourseSets();

// 我的班级（需要认证）
echo $es->myClassrooms();

//根据nickname查询用户(不认证用户)
echo $es->users('nickname', $nickname);

//根据id查询用户(不认证用户)
echo $es->users('id', $id);

//根据email查询用户(不认证用户)
echo $es->users('email', $email);

//根据mobile查询用户(不认证用户)
echo $es->users('mobile', $mobile);

//根据token查询用户，注意token类型的值不是认证token，而是用户的uuid(不认证用户)
echo $es->users('token', $token);
```

### 课程

网校课程是CourseSet，课程计划是Course，一个课程可以有多个教学计划，课时任务是Task，一个教学计划有多个任务

```php

//获取课程列表（不认证用户）
echo $es->courseSets();

//按条件获取课程列表（不认证用户）
$data = [
    'type'   => 'normal', //教学计划类型:normal,live
    'title'  => '音乐', //课程标题
    'sort'   => '-studentNum', //排序方式'createdTime','updatedTime','recommendedSeq','hitNum','recommendedTime','rating','studentNum'，-field代表倒序
    'offset' => 0,
    'limit'  => 100
];
echo $es->courseSets($data);

//获取指定课程信息（不认证用户）
echo $es->courseSets($courseSetId);

//根据课程ID获取课程所有教学计划学员信息（不认证用户）
echo $es->courseSetMembers($courseSetId);

//根据课程计划ID获取单个教学计划信息（不认证用户）
echo $es->courses($courseId);

//获取教学计划列表（不认证用户）
echo $es->courses();

//按条件获取教学计划列表（不认证用户）
$data = [
    'type'   => 'live', //教学计划类型:normal,live
    'title'  => '音乐', //课程标题
    'sort'   => '-studentNum', //排序方式'createdTime','updatedTime','recommendedSeq','hitNum','recommendedTime','rating','studentNum'，-field代表倒序
    'offset' => 0,
    'limit'  => 100
];
echo $es->courses($data);

//根据课程ID获取课程已发布的教学计划信息（不认证用户）
echo $es->courseSet($courseSetId);

//加入教学计划，成为学员(需要认证用户，只可以加免费课)
echo $es->member($courseId);

//根据课程计划ID获取教学计划学员学习信息（不认证用户）
echo $es->courseMembers($courseId);

//根据课程ID获取认证用户的课程计划学习信息(需要认证)
echo $es->myCourseSetMember($courseSetId);

//根据课程计划ID获取认证用户的课程计划学习信息(需要认证)
echo $es->myCourseMember($courseId);

//获取单个任务（不认证用户）
echo $es->courseTask($courseId, $taskId);

//获取教学计划的目录列表（不认证用户）
 echo $es->courseItems($courseId);

//获取计划下的第一个试看任务（不认证用户）
echo $es->firstTrialTask($courseId);

//获取计划的所有评价（不认证用户）
echo $es->courseReviews($courseId);

//获取课程的所有评价（不认证用户）
echo $es->courseSetReviews($courseSetId);

//获取我的教学计划下的学习进度(需要用户认证)
echo $es->myCourseLearningProgress($courseId);

//我收藏的课程(需要认证用户)
echo $es->myFavoriteCourseSets();

//是否收藏课程(需要认证用户)
echo $es->myFavoriteCourseSets($courseSetId);

//收藏课程(需要认证用户)
echo $es->favoriteCourseSet($courseSetId);

//取消收藏课程(需要认证用户)
echo $es->delFavoriteCourseSet($courseSetId);

//退出计划(需要认证用户)
echo $es->exitCourse($courseId, $reason = '');

//学习任务{envet:doing,finish}(需要认证用户)
echo $es->taskEvent($courseId, $taskId, $event, $lastTime);
```

### 班级

```php
//根据班级ID获取班级信息（不需认证用户）
echo $es->classrooms($classroomId);

//获取班级列表（不需要认证）
echo $es->classrooms();

//我的班级个人学习资料(需要认证)
echo $es->myClassroomMember($classroomId);

//获取班级计划（不需要认证）
echo $es->classroomCourses($classroomId);

//加入班级(需要认证)
echo $es->classroomMember($classroomId);
```

### 题库

```php
// 获取题库练习（不需要认证）
echo $es->itemBankExercises($id);

// 获取题库下的学员（不需要认证）
echo $es->itemBankExercisesMembers($id);
```

### 订单

```php
// 下单（需要认证）
return $es->orders($商品ID, $商品类型, $优惠码);
// 支付（需要认证）
return $es->trades($订单号, $支付类型);
```

### 优惠码

```php
// 获取当前用户的优惠码
return $es->coupons();
```

### 移动端API

```php
// 获取移动端频道（不需要认证）
echo $es->appChannels();
```

### 通知

```php
// 获取通知列表(需要认证)
echo $es->notifications();
```

### 公告

```php
// 获取公告列表（不需要认证）
echo $es->announcements();
```

### 资讯

```php
// 获取文章列表（不需要认证用户）
echo $es->articles();

// 获取指定文章（不需要认证用户）
echo $es->article(1);
```

---

## API文档

所有接口及参数说明请阅读API文档了解。

* 接口API文档：[EduSoho REST API](https://api.oiuv.cn/edusoho/Oiuv/EduSohoApi/ES.html)

## License

MIT
