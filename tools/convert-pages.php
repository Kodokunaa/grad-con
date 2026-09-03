<?php

/** One-time, syntax-aware migration. Reads the protected baseline; never runs it. */
require __DIR__.'/../vendor/autoload.php';
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

$root = dirname(__DIR__);
$source = $root.'/.migration-backup/source';
$parser = (new ParserFactory)->createForNewestSupportedVersion();
$printer = new Standard;
function put(string $path, string $text): void
{
    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0755, true);
    } file_put_contents($path, $text);
}
function calln(string $name, array $args = []): Expr\FuncCall
{
    return new Expr\FuncCall(new Name\FullyQualified($name), array_map(fn ($a) => new Node\Arg($a), $args));
}
function literal(Node $node): ?string
{
    if ($node instanceof Scalar\String_) {
        return $node->value;
    }
    if ($node instanceof Expr\BinaryOp\Concat) {
        $a = literal($node->left);
        $b = literal($node->right);

        return $a !== null && $b !== null ? $a.$b : null;
    }
    if ($node instanceof Scalar\MagicConst\Dir) {
        return '@DIR@';
    }

    return null;
}

final class ConvertPage extends NodeVisitorAbstract
{
    public array $functions = [];

    public array $uses = [];

    public array $ddl = [];

    public function __construct(public string $id, public string $folder, array $names)
    {
        $this->functions = $names;
    }

    public function leaveNode(Node $n)
    {
        if ($n instanceof Scalar\MagicConst\Dir) {
            return calln('storage_path', [new Scalar\String_('app/private/files'.($this->folder === '.' ? '' : '/'.$this->folder))]);
        }
        if ($n instanceof Expr\ConstFetch && $n->name->toString() === 'BASE_URL') {
            return calln('url', [new Scalar\String_('')]);
        }
        if ($n instanceof Expr\Variable && $n->name === '_SESSION') {
            return new Expr\PropertyFetch(calln('gc_context'), 'session');
        }
        if ($n instanceof Expr\Variable && $n->name === '_GET') {
            return new Expr\PropertyFetch(calln('gc_context'), 'query');
        }
        if ($n instanceof Expr\Variable && $n->name === '_POST') {
            return new Expr\PropertyFetch(calln('gc_context'), 'post');
        }
        if ($n instanceof Expr\Variable && $n->name === '_SERVER') {
            return new Expr\MethodCall(new Expr\PropertyFetch(calln('request'), 'server'), 'all');
        }
        if ($n instanceof Expr\Variable && $n->name === '_FILES') {
            return calln('gc_files');
        }
        if ($n instanceof Expr\Exit_) {
            return calln('gc_finish', $n->expr ? [$n->expr] : []);
        }
        if ($n instanceof Expr\Include_) {
            $text = (new Standard)->prettyPrintExpr($n->expr);
            if (preg_match('~includes/([a-z_]+)\.php~', $text, $m)) {
                return calln('gc_partial', [new Scalar\String_($m[1]), calln('get_defined_vars')]);
            }

            return new Expr\ConstFetch(new Name('null'));
        }
        if ($n instanceof Stmt\Expression && $n->expr instanceof Expr\FuncCall && $n->expr->name->toString() === 'gc_partial') {
            return new Stmt\Echo_([$n->expr]);
        }
        if ($n instanceof Expr\FuncCall && $n->name instanceof Name) {
            $name = $n->name->toString();
            if ($name === 'move_uploaded_file') {
                $n->name = new Name\FullyQualified('gc_move_upload');
            }
            $map = ['header' => 'gc_header', 'http_response_code' => 'gc_http_status', 'session_start' => 'gc_noop', 'session_name' => 'gc_noop', 'session_regenerate_id' => 'gc_noop', 'session_destroy' => 'gc_noop', 'session_unset' => 'gc_noop', 'verify_password' => 'gc_verify_password', 'hash_password' => 'gc_hash_password', 'make_mailer' => 'gc_make_mailer'];
            if (isset($map[$name])) {
                $n->name = new Name\FullyQualified($map[$name]);
            } elseif (str_starts_with($name, 'require_') && in_array($name, ['require_login', 'require_admin', 'require_alumni', 'require_employer', 'require_alumni_officer'])) {
                return calln('gc_require_role', $name === 'require_login' ? [] : [new Scalar\String_(substr($name, 8))]);
            } elseif (isset($this->functions[$name])) {
                $n->name = new Name\FullyQualified($this->functions[$name]);
            }
            if ($name === 'function_exists' && isset($n->args[0]) && $n->args[0]->value instanceof Scalar\String_ && isset($this->functions[$n->args[0]->value->value])) {
                $n->args[0]->value->value = $this->functions[$n->args[0]->value->value];
            }
            if ($name === 'function_exists' && isset($n->args[0]) && $n->args[0]->value instanceof Scalar\String_ && str_starts_with($n->args[0]->value->value, 'require_')) {
                return new Expr\ConstFetch(new Name('true'));
            }
        }
        if ($n instanceof Stmt\Function_) {
            $n->name = new Node\Identifier($this->functions[$n->name->toString()]);
        }
        if ($n instanceof Stmt\Use_) {
            return NodeVisitor::REMOVE_NODE;
        }
        if ($n instanceof Stmt\Catch_ && $n->var) {
            array_unshift($n->stmts, new Stmt\If_(new Expr\Instanceof_($n->var, new Name\FullyQualified('App\\Support\\PageResponse')), ['stmts' => [new Stmt\Expression(new Expr\Throw_($n->var))]]));
        }
        if ($n instanceof Name && (str_contains($n->toString(), 'PHPMailer') || $n->toString() === 'Exception')) {
            if (str_ends_with($n->toString(), 'PHPMailer')) {
                return new Name\FullyQualified('App\Mail\PageMailer');
            }
            if (str_contains($n->toString(), 'SMTP')) {
                return new Name\FullyQualified('App\Mail\PageMailer');
            }

            return new Name\FullyQualified('Exception');
        }
        if ($n instanceof Name && in_array($n->toString(), ['PDO', 'PDOException', 'Throwable', 'DateTime', 'DateTimeImmutable', 'DateTimeZone', 'Exception'])) {
            return new Name\FullyQualified($n->toString());
        }
        if ($n instanceof Expr\MethodCall && $n->name instanceof Node\Identifier && $n->name->toString() === 'exec') {
            if (isset($n->args[0])) {
                return new Expr\MethodCall(calln('gc_context'), 'schemaChange', [new Node\Arg($n->var), $n->args[0]]);
            }
        }
        if ($n instanceof Expr\MethodCall && $n->var instanceof Expr\Variable && $n->var->name === 'pdo' && $n->name instanceof Node\Identifier) {
            $method = $n->name->toString();
            if (in_array($method, ['beginTransaction', 'commit', 'rollBack'])) {
                return new Expr\StaticCall(new Name\FullyQualified('Illuminate\\Support\\Facades\\DB'), $method, $n->args);
            }
            if ($method === 'inTransaction') {
                return new Expr\BinaryOp\Greater(new Expr\StaticCall(new Name\FullyQualified('Illuminate\\Support\\Facades\\DB'), 'transactionLevel'), new Scalar\Int_(0));
            }
        }
        // Laravel handles password verification; parameterized writes are hashed at the database boundary.
        if ($n instanceof Expr\BinaryOp\NotIdentical && $n->left instanceof Expr\ArrayDimFetch && $n->left->dim instanceof Scalar\String_ && $n->left->dim->value === 'password') {
            return new Expr\BooleanNot(new Expr\StaticCall(new Name\FullyQualified('Illuminate\Support\Facades\Hash'), 'check', [new Node\Arg($n->right), new Node\Arg($n->left)]));
        }
        // Remove embedded SMTP credentials and delivery configuration in favor of Laravel Mail.
        if ($n instanceof Expr\Assign && $n->var instanceof Expr\Variable && in_array($n->var->name, ['smtpPassword', 'smtpUsername', 'smtpHost'])) {
            return new Expr\Assign($n->var, calln('config', [new Scalar\String_(match ($n->var->name) {
                'smtpPassword' => 'mail.mailers.smtp.password', 'smtpUsername' => 'mail.mailers.smtp.username', default => 'mail.mailers.smtp.host'
            }), new Scalar\String_('')]));
        }
        if ($n instanceof Expr\Assign && $n->var instanceof Expr\PropertyFetch && $n->var->name instanceof Node\Identifier && in_array($n->var->name->toString(), ['Password', 'Username', 'Host', 'SMTPDebug', 'SMTPOptions'])) {
            return new Expr\ConstFetch(new Name('null'));
        }
        // PHPMailer credential-validation blocks are irrelevant to the configured Laravel transport.
        if ($n instanceof Stmt\If_) {
            $condition = (new Standard)->prettyPrintExpr($n->cond);
            if (str_contains($condition, '$smtpPassword') || str_contains($condition, '$smtpUsername')) {
                return NodeVisitor::REMOVE_NODE;
            }
        }
        if ($n instanceof Scalar\String_) {
            $n->value = str_replace(['http://localhost/CAPSTONE', '/CAPSTONE/'], ['', '/'], $n->value);
        }
        if ($n instanceof Stmt\InlineHTML) {
            $n->value = str_replace(['/CAPSTONE/', 'http://localhost/CAPSTONE/'], ['/', '/'], $n->value);
        }

        return null;
    }
}

$files = [];
foreach (['admin', 'alumni', 'alumni_officer', 'employer', 'includes'] as $dir) {
    foreach (glob($source.'/'.$dir.'/*.php') as $f) {
        $files[] = $f;
    }
}
foreach (['profile.php', 'archive.php'] as $f) {
    $files[] = $source.'/'.$f;
}
$routes = [];
$inventory = [];
$helpers = "<?php\n// Preserved page-specific presentation and domain helpers; uniquely named to avoid collisions.\n";
foreach ($files as $file) {
    $relative = str_replace('\\', '/', substr($file, strlen($source) + 1));
    if ($relative === 'admin/view_resume.php') {
        continue;
    }
    $id = str_replace(['/', '.php'], ['_', ''], $relative);
    $folder = dirname($relative);
    $code = file_get_contents($file);
    if ($relative === 'admin/jobs_create.php') {
        $code = str_replace('INSERT INTO jobs(title, employer_company,', 'INSERT INTO jobs(title, company, employer_company,', $code);
        $code = str_replace('VALUES(?,?,?,?,?,?,?,?,?)', 'VALUES(?,?,?,?,?,?,?,?,?,?)', $code);
        $code = str_replace("\$title,\r\n                \$employer_company,", "\$title,\r\n                \$employer_company,\r\n                \$employer_company,", $code);
    }
    // Uploaded resumes now use a policy-controlled endpoint rather than the original file reader.
    if ($relative === 'employer/applications.php') {
        $start = strpos($code, "if (isset(\$_GET['view_resume']))");
        $end = strpos($code, 'function e(', $start);
        $code = substr($code, 0, $start).substr($code, $end);
    }
    $ast = $parser->parse($code);
    $finder = new NodeFinder;
    $names = [];
    foreach ($finder->findInstanceOf($ast, Stmt\Function_::class) as $fn) {
        $names[$fn->name->toString()] = 'gc_'.$id.'_'.$fn->name;
    }
    $convert = new ConvertPage($id, $folder, $names);
    $traverser = new NodeTraverser($convert);
    $ast = $traverser->traverse($ast);
    // Hoist named helper functions, including those declared inside guards, out of views/controllers.
    $hoist = new class extends NodeVisitorAbstract
    {
        public array $functions = [];

        public function leaveNode(Node $n)
        {
            if ($n instanceof Stmt\Function_) {
                $this->functions[] = $n;

                return NodeVisitor::REMOVE_NODE;
            }

            return null;
        }
    };
    $ast = (new NodeTraverser($hoist))->traverse($ast);
    $helpers .= $printer->prettyPrint($hoist->functions)."\n";
    if ($folder === 'includes') {
        put($root.'/resources/views/partials/'.basename($relative, '.php').'.blade.php', $printer->prettyPrintFile($ast));

        continue;
    }
    $split = count($ast);
    foreach ($ast as $i => $n) {
        if ($n instanceof Stmt\InlineHTML) {
            $split = $i;
            break;
        }
    }
    $initial = array_slice($ast, 0, $split);
    $tail = array_slice($ast, $split);
    $view = 'pages.'.str_replace('/', '.', substr($relative, 0, -4));
    $class = str_replace(' ', '', ucwords(str_replace('_', ' ', $id))).'Controller';
    $php = $printer->prettyPrint($initial);
    $controller = "<?php\nnamespace App\\Http\\Controllers\\Pages;\n\nfinal class $class extends \\App\\Http\\Controllers\\PageController\n{\n    public function __invoke(\\Illuminate\\Http\\Request \$request)\n    {\n        return \$this->renderPage(function () use (\$request) {\n            \$pdo = gc_context()->pdo();\n".$php."\n            return \$this->pageView('$view', get_defined_vars());\n        });\n    }\n}\n";
    if ($relative === 'employer/applications.php') {
        $controller = str_replace('return $this->renderPage(', "if (\$request->filled('view_resume')) return app(\\App\\Http\\Controllers\\FileController::class)->resume(\$request);\n        return \$this->renderPage(", $controller);
    }
    put($root.'/app/Http/Controllers/Pages/'.$class.'.php', $controller);
    put($root.'/resources/views/'.str_replace('.', '/', $view).'.blade.php', $printer->prettyPrintFile($tail));
    $role = in_array($folder, ['admin', 'alumni', 'employer', 'alumni_officer']) ? $folder : null;
    $middleware = $role ? 'account:'.$role : 'account';
    $routes[] = "Route::match(['GET', 'POST'], '/$relative', \\App\\Http\\Controllers\\Pages\\$class::class)->middleware('$middleware')->name('".str_replace('/', '.', substr($relative, 0, -4))."');";
    $inventory[] = ['source' => $relative, 'controller' => $class, 'view' => $view, 'role' => $role ?? 'authenticated'];
}
put($root.'/app/Support/page-functions.php', $helpers);
put($root.'/routes/pages.php', "<?php\nuse Illuminate\\Support\\Facades\\Route;\n".implode("\n", $routes)."\n");
put($root.'/docs/route-inventory.json', json_encode($inventory, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Preserve the complete existing authentication screen markup, replacing only request handling.
foreach (['index.php' => 'login', 'register.php' => 'register', 'forgot_password.php' => 'forgot', 'reset_password.php' => 'reset'] as $file => $name) {
    $code = file_get_contents($source.'/'.$file);
    $start = strpos($code, '<!DOCTYPE');
    $tail = substr($code, $start);
    $tail = preg_replace('/\bBASE_URL\b/', "url('')", $tail);
    $tail = str_replace(['http://localhost/CAPSTONE/', '/CAPSTONE/'], ['/', '/'], $tail);
    put($root.'/resources/views/auth/'.$name.'.blade.php', $tail);
}
echo 'Converted '.count($inventory)." pages plus shared layouts and authentication templates.\n";
