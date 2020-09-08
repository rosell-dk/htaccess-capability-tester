<?php

namespace HtaccessCapabilityTester\Testers;

/**
 * Class for testing if a module is loaded.
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since 0.7
 */
class ModLoadedTester extends CustomTester
{

    /* @var string A valid Apache module name (ie "rewrite") */
    protected $modName;


    private function getServerSignatureBasedTest()
    {
        // Test files, method : Using ServerSignature
        // --------------------------------------------------
        // Requires (in order not to be inconclusive):
        // - Override: All
        // - Status: Core
        // - Directives: ServerSignature, IfModule
        // - PHP?: Yes

        $php = <<<'EOD'
<?php
if (isset($_SERVER['SERVER_SIGNATURE']) && ($_SERVER['SERVER_SIGNATURE'] != '')) {
    echo 1;
} else {
    echo 0;
}
EOD;

        $htaccess = <<<'EOD'
# The beauty of this trick is that ServerSignature is available in core.
# (it requires no modules and cannot easily be made forbidden)
# However, it requires PHP to check for the effect

ServerSignature Off
<IfModule mod_xxx.c>
ServerSignature On
</IfModule>
EOD;

        $htaccess = str_replace('mod_xxx', 'mod_' . $this->modName, $htaccess);

        return [
            'requirements' => ['htaccessEnabled()'],
            'subdir' => 'server-signature',
            'files' => [
                ['.htaccess', $htaccess],
                ['test.php', $php],
            ],
            'request' => 'test.php',
            'interpretation' => [
                ['success', 'body', 'equals', '1'],
                ['failure', 'body', 'equals', '0'],
                // This time we do not fail for 500 because it is very unlikely that any of the
                // directives used are forbidden
            ]
        ];
    }

    /**
     *  @return  array
     */
    private function getRewriteBasedTest()
    {
        // Test files, method: Using Rewrite
        // --------------------------------------------------
        // Requires (in order not to be inconclusive)
        // - Module: mod_rewrite
        // - Override: FileInfo
        // - Directives: RewriteEngine, RewriteRule and IfModule
        // - PHP?: No

        $htaccess = <<<'EOD'
RewriteEngine On
<IfModule mod_xxx.c>
    RewriteRule ^request-me\.txt$ 1.txt [L]
</IfModule>
<IfModule !mod_xxx.c>
    RewriteRule ^request-me\.txt$ 0.txt [L]
</IfModule>
EOD;

        $htaccess = str_replace('mod_xxx', 'mod_' . $this->modName, $htaccess);

        return [
            'requirements' => ['canRewrite()'],
            'subdir' => 'rewrite',
            'files' => [
                ['.htaccess', $htaccess],
                ['0.txt', '0'],
                ['1.txt', '1'],
                ['request-me.txt', 'Redirect failed even though rewriting has been proven to work. Strange!'],
            ],
            'request' => 'request-me.txt',
            'interpretation' => [
                ['success', 'body', 'equals', '1'],
                ['failure', 'body', 'equals', '0'],
                //['inconclusive', 'status-code', 'not-equals', '200'],
            ]
        ];
    }

    /**
     *  @return  array
     */
    private function getResponseHeaderBasedTest()
    {

        // Test files, method: Using Response Header
        // --------------------------------------------------
        // Requires (in order not to be inconclusive)
        // - Module: mod_headers
        // - Override: FileInfo
        // - Directives: Header and IfModule
        // - PHP?: No

        $htaccess = <<<'EOD'
<IfModule mod_xxx.c>
    Header set X-Response-Header-Test: 1
</IfModule>
<IfModule !mod_xxx.c>
    Header set X-Response-Header-Test: 0
</IfModule>
EOD;

        $htaccess = str_replace('mod_xxx', 'mod_' . $this->modName, $htaccess);

        return [
            'requirements' => ['canSetResponseHeader()'],
            'subdir' => 'response-header',
            'files' => [
                ['.htaccess', $htaccess],
                ['request-me.txt', 'thanks'],
            ],
            'request' => 'request-me.txt',
            'interpretation' => [
                ['success', 'headers', 'contains-key-value', 'X-Response-Header-Test', '1'],
                ['failure', 'headers', 'contains-key-value', 'X-Response-Header-Test', '0'],
            ]
        ];
    }

    /**
     *  @return  array
     */
    private function getContentDigestBasedTest()
    {
        // Test files, method: Using ContentDigest
        // --------------------------------------------------
        //
        // Requires (in order not to be inconclusive)
        // - Module: None - its in core
        // - Override: Options
        // - Directives: ContentDigest
        // - PHP?: No

        $htaccess = <<<'EOD'
<IfModule mod_xxx.c>
    ContentDigest On
</IfModule>
<IfModule !mod_xxx.c>
    ContentDigest Off
</IfModule>
EOD;

        $htaccess = str_replace('mod_xxx', 'mod_' . $this->modName, $htaccess);

        return [
            'requirements' => ['canContentDigest()'],
            'subdir' => 'content-digest',
            'files' => [
                ['.htaccess', $htaccess],
                ['request-me.txt', 'thanks'],
            ],
            'request' => 'request-me.txt',
            'interpretation' => [
                ['success', 'headers', 'contains-key', 'Content-MD5'],
                ['failure', 'headers', 'not-contains-key', 'Content-MD5'],
            ]
        ];
    }

    /**
     *  @return  array
     */
    private function getDirectoryIndexBasedTest()
    {
        // Test files, method: Using DirectoryIndex
        // --------------------------------------------------
        //
        // Requires (in order not to be inconclusive)
        // - Module: mod_dir (Status: Base)
        // - Override: Indexes
        // - Directives: DirectoryIndex
        // - PHP?: No

        $htaccess = <<<'EOD'
<IfModule mod_xxx.c>
    DirectoryIndex 1.html
</IfModule>
<IfModule !mod_xxx.c>
    DirectoryIndex 0.html
</IfModule>
EOD;

        $htaccess = str_replace('mod_xxx', 'mod_' . $this->modName, $htaccess);

        return [
            'requirements' => ['canSetDirectoryIndex()'],
            'subdir' => 'directory-index',
            'files' => [
                ['.htaccess', $htaccess],
                ['0.html', '0'],
                ['1.html', '1'],
            ],
            'request' => '',        // empty - in order to request the index
            'interpretation' => [
                ['success', 'body', 'equals', '1'],
                ['failure', 'body', 'equals', '0'],
            ]
        ];
    }


    /**
     *  @return  array
     */
    private function getAddTypeBasedTest()
    {
        // Test files, method: Using AddType
        // --------------------------------------------------
        //
        // Requires (in order not to be inconclusive)
        // - Module: mod_mime
        // - Override: FileInfo
        // - Directives: AddType and IfModule
        // - PHP?: No

        $htaccess = <<<'EOD'
<IfModule mod_xxx.c>
    AddType image/gif .test
</IfModule>
<IfModule !mod_xxx.c>
    AddType image/jpeg .test
</IfModule>
EOD;

        $htaccess = str_replace('mod_xxx', 'mod_' . $this->modName, $htaccess);

        return [
            'requirements' => ['canAddType()'],
            'subdir' => 'add-type',
            'files' => [
                ['.htaccess', $htaccess],
                ['request-me.test', '0'],
            ],
            'request' => 'request-me.test',
            'interpretation' => [
                ['success', 'headers', 'contains-key-value', 'Content-Type', 'image/gif'],
                ['failure', 'headers', 'contains-key-value', 'Content-Type', 'image/jpeg'],
            ]
        ];
    }


    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct($moduleName)
    {
        $this->modName = $moduleName;

        $tests = [
            'subdir' => 'mod-loaded-tester/' . $this->modName,
            'subtests' => [
                $this->getServerSignatureBasedTest(),   // PHP
                $this->getContentDigestBasedTest(),     // Override: Options
                $this->getAddTypeBasedTest(),           // Override: FileInfo, Status: Base (mod_mime)
                $this->getDirectoryIndexBasedTest(),    // Override: Indexes, Status: Base (mod_dir)
                $this->getRewriteBasedTest(),           // Override: FileInfo, Module: mod_rewrite
                $this->getResponseHeaderBasedTest()     // Override: FileInfo, Module: mod_headers
            ]
        ];

        parent::__construct($tests);
    }
}
