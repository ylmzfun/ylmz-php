# Ylmz PHP Framework

轻量级 PHP MVC 框架，支持 **MySQL**、**Redis**、**消息队列**，开箱即用。

## 特性

- 🚀 **轻量级** — 核心 < 50 个文件，无臃肿依赖
- 🧩 **MVC 架构** — 清晰的 Controller / Model / View 分层
- 🗄️ **MySQL** — 基于 Medoo，简洁的数据库操作
- 🔴 **Redis** — 缓存驱动 + 队列驱动，自动切换
- 📬 **消息队列** — Job 推入 / Worker 消费 / 延迟任务 / 失败重试
- 🎨 **Twig 模板** — 布局继承、变量渲染
- 🧱 **中间件** — Auth / CORS / CSRF 开箱即用
- 🔧 **CLI 工具** — 代码生成、开发服务器、队列 Worker
- 📦 **Composer** — PSR-4 自动加载，`composer create-project` 一键安装

## 环境要求

- PHP >= 8.0
- Composer
- MySQL（可选）
- Redis（可选，缓存 & 队列需要 `ext-redis`）

## 快速开始

### 方式一：composer create-project（推荐）

```bash
composer create-project ylmz/framework my-project
cd my-project
php ylmz serve
```

### 方式二：从已有项目生成

```bash
cd /path/to/ylmz-framework
php ylmz new ../my-project
cd ../my-project
php ylmz serve
```

## 目录结构

```
my-project/
├── .env                    # 环境配置
├── composer.json           # Composer 依赖
├── ylmz                    # CLI 命令行入口
├── router.php              # 内置服务器路由
├── index.php               # Web 入口
│
├── core/                   # 框架核心
│   ├── Application.php     # 应用主类
│   ├── Container.php       # IoC 容器
│   ├── Router.php          # 路由（显式 + 自动）
│   ├── Controller.php      # 控制器基类
│   ├── Model.php           # 模型基类（Medoo）
│   ├── Config.php          # 配置管理（.env）
│   ├── Redis.php           # Redis 连接管理
│   ├── Validator.php       # 输入验证
│   ├── ExceptionHandler.php# 统一异常处理
│   ├── ServiceProvider.php # 服务提供者
│   ├── Debug.php           # 调试（Whoops）
│   ├── Http/               # Request / Response / Middleware
│   ├── Cache/              # 缓存驱动（File / Redis / DB）
│   ├── Log/                # 日志驱动（File / DB）
│   ├── Queue/              # 队列（Job / Queue / Worker）
│   └── Console/            # CLI 命令
│
├── app/                    # 应用层
│   ├── Ctrl/               # 控制器
│   ├── Model/              # 模型
│   ├── Middleware/         # 中间件
│   ├── Job/                # 队列任务
│   ├── Provider/           # 服务提供者
│   └── view/               # Twig 模板
│
├── runtime/                # 运行时（日志/缓存）
└── public/                 # 静态资源
```

## CLI 命令

```bash
# 项目管理
php ylmz new <name>                # 创建新项目

# 代码生成
php ylmz make:controller <Name>    # 生成控制器
php ylmz make:model <Name>         # 生成模型
php ylmz make:middleware <Name>    # 生成中间件
php ylmz make:provider <Name>      # 生成服务提供者
php ylmz make:job <Name>           # 生成队列任务

# 运行
php ylmz serve [host] [port]       # 开发服务器
php ylmz queue:work [queue]        # 启动队列 Worker
php ylmz queue:clear [queue]       # 清空队列
php ylmz routes                    # 查看路由
```

## 路由

### 显式路由（推荐）

在 `app/Provider/RouteServiceProvider.php` 中注册：

```php
$router = app()->getRouter();

// 基础路由
$router->get('/posts', [PostCtrl::class, 'index']);
$router->post('/posts', [PostCtrl::class, 'store']);
$router->get('/posts/{id}', [PostCtrl::class, 'show']);

// 带中间件的路由组
$router->group([Auth::class], function ($router) {
    $router->get('/admin/dashboard', [AdminCtrl::class, 'index']);
    $router->get('/admin/users', [AdminCtrl::class, 'users']);
});
```

### 自动路由

未匹配显式路由时，自动映射 URL 到控制器：

```
GET /user/profile  →  App\Ctrl\UserCtrl::profile()
GET /              →  App\Ctrl\IndexCtrl::index()
```

## 控制器

```php
<?php
namespace App\Ctrl;

use Ylmz\Controller;
use Ylmz\Http\Request;
use Ylmz\Http\Response;

class PostCtrl extends Controller
{
    public function index(Request $request): Response
    {
        $posts = \App\Model\Post::db()
            ->select('post', '*', ['LIMIT' => 10]);

        return $this->json($posts);
    }

    public function show(Request $request): Response
    {
        $id = $request->input('id');
        // ...查询逻辑

        $this->assign('title', '文章详情');
        $this->assign('post', $post);
        return $this->display('post/show.html');
    }
}
```

## 模型（MySQL）

```php
<?php
namespace App\Model;

use Ylmz\Model;

class Post extends Model
{
    protected string $table = 'post';

    public function getPublished(): array
    {
        return self::db()->select($this->table, '*', [
            'status' => 1,
            'ORDER' => ['id' => 'DESC'],
            'LIMIT' => 20,
        ]);
    }
}
```

Medoo 完整用法见：https://medoo.in/doc

## Redis 缓存

在 `.env` 中设置 `CACHE_DRIVER=redis`：

```php
use Ylmz\Cache;

Cache::set('user:1', $userData, 3600);    // 写入，3600秒过期
$user = Cache::get('user:1');              // 读取
Cache::delete('user:1');                   // 删除
Cache::clear();                            // 清空当前库
```

缓存键自动带 `REDIS_PREFIX` 前缀。

## 消息队列

### 创建 Job

```bash
php ylmz make:job SendEmail
```

```php
<?php
namespace App\Job;

use Ylmz\Queue\Job;

class SendEmail extends Job
{
    public function handle(): void
    {
        $to = $this->payload['to'];
        $subject = $this->payload['subject'];
        mail($to, $subject);
    }
}
```

### 推送 Job

```php
use Ylmz\Queue\Queue;

$queue = new Queue('default');

// 即时执行
$queue->push(Job\SendEmail::class, [
    'to' => 'user@example.com',
    'subject' => 'Welcome!',
]);

// 延迟 5 分钟执行
$queue->push(Job\SendEmail::class, [
    'to' => 'user@example.com',
    'subject' => 'Reminder',
], delay: 300);
```

### 启动 Worker

```bash
# 处理 default 队列
php ylmz queue:work

# 处理指定队列
php ylmz queue:work emails
```

Worker 特性：
- 失败自动重试（默认 3 次）
- 超限进入失败队列
- 支持延迟任务（zset 调度）

## 中间件

框架内置三个中间件：

| 中间件 | 功能 |
|---|---|
| `App\Middleware\Auth` | 认证检查 |
| `App\Middleware\Cors` | 跨域请求 |
| `App\Middleware\Csrf` | CSRF 令牌验证 |

自定义中间件：

```bash
php ylmz make:middleware Throttle
```

```php
class Throttle implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // 前置逻辑
        $response = $next($request);
        // 后置逻辑
        return $response;
    }
}
```

## 输入验证

```php
$validator = new \Ylmz\Validator($request->all());

$validator
    ->required('name', '用户名不能为空')
    ->email('email')
    ->min('age', 18)
    ->max('content', 5000);

if ($validator->fails()) {
    return $this->json([
        'error' => $validator->firstError()
    ], 422);
}
```

## 配置说明 (.env)

```ini
# 应用
APP_NAME=Ylmz
APP_DEBUG=true                  # 开发模式（Whoops 错误页）

# MySQL
DB_TYPE=mysql
DB_HOST=localhost
DB_NAME=demo

# Redis（可选）
REDIS_HOST=127.0.0.1
REDIS_PREFIX=ylmz:

# 驱动选择
CACHE_DRIVER=file               # file | redis
QUEUE_DRIVER=redis              # 当前仅 redis
LOG_DRIVER=file                 # file | db
```

## License

MIT
