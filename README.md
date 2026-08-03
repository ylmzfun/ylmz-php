# Ylmz PHP Framework

轻量级 PHP MVC 框架，支持 **MySQL**、**Redis**、**消息队列**、**定时任务**、**JWT 认证**、**数据库迁移**。

## 特性

- 🚀 **轻量级** — 核心 62 文件，零臃肿依赖
- 🧩 **MVC** — Controller / Model / View 清晰分层
- 🗄️ **MySQL** — Medoo ORM，简洁查询
- 🔴 **Redis** — 缓存驱动 + 队列驱动
- 📬 **消息队列** — push / delay / retry / Worker daemon
- ⏰ **定时任务** — cron 调度器
- 🔐 **认证** — JWT (纯 PHP，零依赖)
- 🔒 **安全** — CSRF / XSS 过滤 / AES-256 加密 / bcrypt 哈希
- 🎨 **模板** — Twig 引擎
- 🧱 **中间件** — 请求管道，Auth / CORS / CSRF
- ✅ **验证** — 16 条规则，链式调用
- 🌍 **多语言** — trans() / trans_choice()
- � **邮件** — SMTP / sendmail 双驱动
- �🔧 **CLI** — 18 个命令，代码生成，开发服务器
- 📦 **安装** — `composer create-project` 一键

## 环境要求

- PHP >= 8.0
- Composer
- MySQL (可选)
- Redis (可选，需 `ext-redis`)

## 快速开始

```bash
composer create-project ylmz/framework my-project
cd my-project
cp .env.example .env
php ylmz key:generate
php ylmz serve
```

打开 http://localhost:8000

## 目录结构

```
my-project/
├── .env                       # 环境配置
├── composer.json              # Composer
├── ylmz                       # CLI 入口
├── index.php                  # Web 入口
│
├── core/                      # 框架核心
│   ├── run.php                # 引导
│   ├── Controller.php         # 控制器基类
│   ├── Model.php              # 模型基类
│   ├── Router.php             # 路由
│   ├── Schema.php             # 数据库迁移
│   ├── Cache.php / Log.php    # 门面
│   ├── CacheDriver.php        # 缓存接口
│   ├── LogDriver.php          # 日志接口
│   ├── Foundation/            # 应用核心
│   │   ├── Application.php    # 主类
│   │   ├── Container.php      # IoC 容器
│   │   ├── Config.php         # 配置
│   │   ├── ServiceProvider.php# 服务提供者
│   │   └── ExceptionHandler.php
│   ├── Support/               # 工具类 (17)
│   │   ├── Redis.php / Crypt.php / Hash.php
│   │   ├── Session.php / Auth.php / Event.php
│   │   ├── Validator.php / Collection.php
│   │   ├── Mail.php / Lang.php / Str.php
│   │   ├── Schedule.php / RateLimiter.php
│   │   ├── HttpClient.php / FileUpload.php
│   │   ├── Pagination.php / Debug.php
│   ├── Http/                  # Request / Response / Middleware
│   ├── Cache/                 # 缓存驱动 (File/Redis/DB)
│   ├── Log/                   # 日志驱动 (File/DB)
│   ├── Queue/                 # Job / Queue / Worker
│   ├── Console/               # CLI 命令 (18)
│   └── Common/                # 辅助函数 (20)
│
├── app/                       # 应用层
│   ├── Ctrl/                  # 控制器
│   ├── Model/                 # 模型
│   ├── Middleware/            # 中间件
│   ├── Job/                   # 队列 Job
│   ├── Command/               # 自定义命令
│   ├── Provider/              # 服务提供者
│   ├── Migration/             # 数据库迁移
│   ├── lang/                  # 多语言文件
│   └── view/                  # Twig 模板
│
├── runtime/                   # 日志/缓存
└── public/                    # 静态资源
```

## CLI 命令

```bash
# 项目管理
php ylmz new <name>                # 创建新项目

# 代码生成
php ylmz make:controller <Name>    # 控制器
php ylmz make:model <Name>         # 模型
php ylmz make:middleware <Name>    # 中间件
php ylmz make:provider <Name>      # 服务提供者
php ylmz make:job <Name>           # 队列 Job
php ylmz make:migration <Name>     # 迁移文件
php ylmz make:command <Name>       # 自定义命令

# 数据库
php ylmz migrate                   # 执行迁移
php ylmz migrate:rollback          # 回滚
php ylmz migrate:status            # 状态

# 运行
php ylmz serve [host] [port]       # 开发服务器
php ylmz queue:work [queue]        # 队列 Worker
php ylmz queue:clear [queue]       # 清空队列
php ylmz schedule:run              # 定时任务
php ylmz routes                    # 路由列表
php ylmz key:generate              # 生成 APP_KEY
```

## 路由

```php
// 基础路由
$router->get('/posts', [PostCtrl::class, 'index']);
$router->post('/posts', [PostCtrl::class, 'store']);
$router->get('/posts/{id}', [PostCtrl::class, 'show']);

// 中间件 + 前缀
$router->prefix('/api/v1')->group([Cors::class], function ($r) {
    $r->get('/users', [UserCtrl::class, 'index']);
});
```

## 控制器

```php
namespace App\Ctrl;
use Ylmz\Controller;
use Ylmz\Http\Request;
use Ylmz\Http\Response;

class PostCtrl extends Controller
{
    public function index(Request $request): Response
    {
        return $this->json(['posts' => []]);
    }

    public function show(Request $request): Response
    {
        $this->assign('post', ['title' => 'Hello']);
        return $this->display('post/detail.html');
    }
}
```

## 模型 (MySQL)

```php
namespace App\Model;
use Ylmz\Model;

class Post extends Model
{
    public function published(): array
    {
        return self::db()->select('post', '*', [
            'status' => 1,
            'ORDER' => ['id' => 'DESC'],
            'LIMIT' => 20,
        ]);
    }
}
```

## 认证 (JWT)

```php
// 登录
$token = Auth::attempt(['email' => 'a@b.com', 'password' => 'secret']);
// 返回 JWT 字符串或 null

// 获取当前用户
$user = Auth::user();
$userId = Auth::id();

// 中间件保护路由
$router->group([AuthMiddleware::class], function ($r) {
    $r->get('/profile', [UserCtrl::class, 'profile']);
});
```

## 消息队列

```php
// 推送
$queue = new Queue('default');
$queue->push(SendEmailJob::class, ['to' => 'a@b.com'], delay: 300);

// Worker
// php ylmz queue:work
```

## 定时任务

```php
// app/schedule.php
schedule()->command('php ylmz queue:work')->everyMinute();
schedule()->call(fn() => Log::info('cleanup'))->daily();
// cron: * * * * * php ylmz schedule:run
```

## 数据库迁移

```bash
php ylmz make:migration create_users_table
php ylmz migrate
php ylmz migrate:rollback
```

## 缓存

```php
Cache::set('key', $data, 3600);
$data = Cache::get('key');
Cache::remember('users', 60, fn() => Model::db()->select('users', '*'));
```

## 验证

```php
$v = new Validator($request->all());
$v->required('email')->email('email')
  ->min('password', 6)
  ->confirmed('password');
if ($v->fails()) return $this->json(['error' => $v->firstError()], 422);
```

## 邮件

```php
Mail::new()
    ->to('user@example.com')
    ->subject('Welcome')
    ->html('<h1>Hello</h1>')
    ->send();
```

## 辅助函数 (20)

```php
app()  config()  view()  collect()  encrypt()  decrypt()
bcrypt()  csrf_token()  csrf_field()  session()  old()
redirect()  abort()  route()  event()  now()
trans()  __()  xss()  schedule()  rate_limiter()
```

## 配置 (.env)

```ini
APP_DEBUG=true
DB_TYPE=mysql
DB_HOST=localhost
DB_NAME=demo

REDIS_HOST=127.0.0.1
CACHE_DRIVER=file          # file | redis | db
QUEUE_DRIVER=redis
MAIL_DRIVER=sendmail       # sendmail | smtp
```

## License

MIT
