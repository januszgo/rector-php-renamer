<?php

declare(strict_types=1);

namespace App\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class PrefixInfRector extends AbstractRector
{
    private string $prefix = 'inf_';

    public function getNodeTypes(): array
    {
        return [
            Class_::class,
            Function_::class,
            ClassMethod::class,
            MethodCall::class,
            StaticCall::class,
            New_::class,
            FuncCall::class,
        ];
    }

    public function refactor(Node $node)
    {
        // ------------------------
        // Prefiks dla metod w klasach
        if ($node instanceof ClassMethod) {
            if ($node->name instanceof Identifier && in_array($node->name->toString(), ['__construct', '__destruct'])) {
                return null;
            }
            if ($node->name instanceof Identifier && !$this->isNamePrefixed($node->name->toString())) {
                $node->name->name = $this->prefix . $node->name->toString();
            }
        }

        // ------------------------
        // Prefiks dla klas
        if ($node instanceof Class_) {
            if ($node->name && !$this->isNamePrefixed($node->name->toString())) {
                $node->name->name = $this->prefix . $node->name->toString();
            }
        }

        // ------------------------
        // Prefiks dla funkcji globalnych (deklaracje)
        if ($node instanceof Function_) {
            if ($node->name && !$this->isNamePrefixed($node->name->toString())) {
                $node->name->name = $this->prefix . $node->name->toString();
            }
        }

        // ------------------------
        // Prefiks dla wywołań metod -> i :: 
        if ($node instanceof MethodCall) {
            if ($node->name instanceof Identifier) {
                $methodName = $node->name->toString();
                if (!in_array($methodName, ['__construct', '__destruct']) && !$this->isNamePrefixed($methodName)) {
                    $node->name->name = $this->prefix . $methodName;
                }
            }
        }

        if ($node instanceof StaticCall) {
            // Prefiks klasy jeśli nie self/parent/static
            if ($node->class instanceof Name) {
                $className = $node->class->toString();
                if (!in_array(strtolower($className), ['self', 'static', 'parent']) && !$this->isNamePrefixed($className) && !$this->isInternalPhpClass($className)) {
                    $node->class = new Name($this->prefix . $className);
                }
            }
            // Prefiks metody
            if ($node->name instanceof Identifier) {
                $methodName = $node->name->toString();
                if (!in_array($methodName, ['__construct', '__destruct']) && !$this->isNamePrefixed($methodName)) {
                    $node->name->name = $this->prefix . $methodName;
                }
            }
        }

        // ------------------------
        // Prefiks dla new ClassName()
        if ($node instanceof New_) {
            if ($node->class instanceof Name) {
                $className = $node->class->toString();
                if (!in_array(strtolower($className), ['self', 'static', 'parent'])
                    && !$this->isNamePrefixed($className)
                    && !$this->isInternalPhpClass($className)
                ) {
                    $node->class = new Name($this->prefix . $className);
                }
            }
        }

        // ------------------------
        // Prefiks dla wywołań funkcji globalnych (FuncCall)
        if ($node instanceof FuncCall) {
            if ($node->name instanceof Name) {
                $funcName = $node->name->toString();
                // tylko Twoje funkcje inf_* lub nowe, nie wbudowane PHP
                if (!$this->isNamePrefixed($funcName) && !$this->isInternalPhpFunction($funcName)) {
                    $node->name = new Name($this->prefix . $funcName);
                }
            }
        }

        return $node;
    }

    private function isNamePrefixed(string $name): bool
    {
        return str_starts_with($name, $this->prefix);
    }

    private function isInternalPhpClass(string $className): bool
    {
        return class_exists($className, false) || interface_exists($className, false) || trait_exists($className, false);
    }

    private function isInternalPhpFunction(string $name): bool
    {
        // jeśli funkcja zaczyna się od inf_, to NIE jest wbudowana i można prefiksować
        if (str_starts_with($name, 'inf_')) {
            return false;
        }
        // wbudowane funkcje PHP -> NIE prefiksować
        return function_exists($name);
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Adds prefix "inf_" to all classes, methods, functions and their calls, excluding constructors/destructors, self/parent/static, internal PHP classes, and internal PHP functions.',
            []
        );
    }
}