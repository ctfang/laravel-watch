# laravel 业务函数委托执行

函数委托执行，特别适用于扩展业务时又不需要侵入原有代码。

# 安装
~~~~shell
composer require ctfang/laravel-watch
~~~~
如果使用Phpstorm, 可以安装扩展 `PHP Annotations` 方便提示。
# 使用

在app目录下任意类的函数注释上加 `@Watch(test::class,"func")` 就会自动经过 `test->func`。

1。先设计一个场景，一个登陆需求。
原来代码是这样的，只实现了登陆功能，成功返回用户信息。
~~~~php
<?php


namespace App\Http\Controllers\User;


use Illuminate\Http\Request;

class LoginController
{
    public function login(Request $request)
    {
        // 登陆成功
        return ['request' => $request , 'user_id' => 1];
    }
}
~~~~
2。需求有变动，需要加上登陆流水、登陆途径和流失记录（登陆失败也记录）

新增逻辑类 `LoginLogic`, 实现逻辑

~~~~php
<?php


namespace App\Logics\User;


use Ctfang\LaravelWatch\Context;

class LoginLogic
{
    /**
     * 记录上传参数
     * @param  \Ctfang\LaravelWatch\Context  $context
     * @param  \Closure  $next
     * @return mixed
     */
    public function watchLogin(Context $context, \Closure $next)
    {
        /** @var \Illuminate\Http\Request $request 输入参数获取 */
        $request = $context->getInput('request');

        // 逻辑处理

        // 途径处理完后再登陆
        return $next($context);
    }

    /**
     * 记录登陆日记
     * @param  \Ctfang\LaravelWatch\Context  $context
     * @param  \Closure  $next
     * @return mixed
     */
    public function loginLog(Context $context, \Closure $next)
    {
        // 登陆成功后才记录流失
        $response = $next($context);
        $userId   = $response['user_id'];

        // 记录逻辑
        
        return $response;
    }
}
~~~~
修改 `LoginController` 文件新增注释代码, 注意注释的类使用也是要`use`进来的
~~~~php
<?php


namespace App\Http\Controllers\User;


use Illuminate\Http\Request;
use Ctfang\LaravelWatch\Annotations\Watch;
use App\Logics\User\LoginLogic;

class LoginController
{
    /**
     * @Watch(LoginLogic::class,"watchLogin")
     * @Watch(LoginLogic::class,"loginLog")
     */
    public function login(Request $request)
    {
        return ['request' => $request , 'user_id' => 1];
    }
}
~~~~

# 注意

为了更方便委托，`logic`类必须需要make方式或者`laravel`注入方式实例类

例如一个查看用户`info`的函数需要代理，`controller` 函数需要这样子写,`LoginLogic `需要注入或者`app('LoginLogic')`
~~~~php
    public function info(Request $request, LoginLogic $logic)
    {
        $userId = $request->input('user_id');

        return ['user' => $logic->info($userId)];
    }
~~~~
`info`的函数代码

~~~~php

    /**
     * @Watch(LoginLogic::class,"infoWatch")
     */
    public function info(int $id)
    {
        return [];
    }

    public function infoWatch(Context $context, \Closure $next)
    {
        $id = $context->getInput('id');

        echo "玩家被读取" . $id;
    }
~~~~
