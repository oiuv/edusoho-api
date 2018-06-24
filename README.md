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
echo $es->courseSets(1234);
```

> 获取课程所有学员（去重）
```php
//根据课程ID获取课程所有教学计划学员信息
echo $es->courseSetMembers(1234);
```

> 教学计划信息
```php
//根据课程计划ID获取单个教学计划信息
echo $es->courses(1234);

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
echo $es->courseSet(1234);
```

> 教学计划成员信息
```php
//根据课程计划ID获取教学计划学员学习信息
echo $es->courseMembers(1234);

//需要认证
//根据课程ID获取认证用户的课程计划学习信息
echo $es->myCourseSetMember(1234);

//需要认证
//根据课程计划ID获取认证用户的课程计划学习信息
echo $es->myCourseMember(1234);
```

> 更多方法看源码并参考[EduSoho REST API](http://developer.edusoho.com/api/)

## License

MIT
