pipeline {
    agent any

    options {
        disableConcurrentBuilds(abortPrevious: true)
        timeout(time: 1, unit: 'HOURS')
    }

    environment {
        GITHUB_CREDENTIALS_ID = 'faveobot'
        MYSQL_CREDENTIALS_ID  = 'mysql_credentials_id'
        REPO_NAME             = 'faveo-helpdesk'
        SONAR_HOST_URL        = 'https://sonarqube.faveotools.com'

        // Backend tests run and report, but do NOT fail the build yet: the existing
        // suite is flaky (leftover DB state gives 1-3 failures on identical runs).
        // Flip to 'true' once it is stable, and add 'Jenkins / Backend Tests' to the
        // branch-protection required checks.
        BACKEND_TESTS_BLOCKING = 'false'
    }

    stages {

        stage('Checkout') {
            steps {
                script {
                    try {
                        def prNumber = env.CHANGE_ID ?: env.GITHUB_PR_NUMBER
                        if (!prNumber) { error('No PR number — this pipeline only runs for pull requests') }
                        retry(3) {
                            checkout([$class: 'GitSCM',
                                branches: [[name: "origin/PR-${prNumber}"]],
                                extensions: [[$class: 'CloneOption', depth: 1, shallow: true, noTags: true, timeout: 60]],
                                userRemoteConfigs: [[
                                    url: "https://github.com/faveosuite/${REPO_NAME}.git",
                                    credentialsId: env.GITHUB_CREDENTIALS_ID,
                                    refspec: "+refs/pull/${prNumber}/head:refs/remotes/origin/PR-${prNumber}"
                                ]]
                            ])
                        }
                    } catch (err) {
                        notifyInfraFailure('Checkout', "Git clone failed after 3 retries — ${err.message?.take(200) ?: 'network error'}")
                        throw err
                    }
                }
            }
        }

        stage('Rebase check') {
            steps {
                script {
                    try {
                        checkRebaseOnDevelopment()
                    } catch (err) {
                        env.REBASE_CHECK_RESOLVED = 'true'
                        throw err
                    }
                }
            }
        }

        stage('Detect changed files') {
            steps {
                script {
                    try {
                        detectChangedFiles()
                    } catch (err) {
                        notifyInfraFailure('Detect changed files', "Failed to compute diff vs development — ${err.message?.take(200) ?: 'error'}")
                        throw err
                    }
                }
            }
        }

        // Autoload integrity. vendor/ is committed in this repository, so a PR that
        // adds a class without refreshing the classmap breaks autoloading at runtime.
        stage('Autoload check') {
            when { expression { env.BACKEND_CHANGED == 'true' } }
            steps { script { runAutoloadCheck() } }
        }

        // ── Code quality ─────────────────────────────────────────────────────────
        // Larastan and Semgrep always run to completion. Failures are collected and
        // reported together in the gate stage below.
        //
        // ESLint is deliberately absent: Faveo Community has no ESLint config, no
        // ESLint dependency and no first-party JavaScript — only webpack.mix.js and
        // gulpfile.js, which are build configuration.

        stage('Code quality') {
            parallel {

                stage('Larastan') {
                    when { expression { env.BACKEND_CHANGED == 'true' } }
                    steps { script { runLarastan() } }
                }

                stage('Semgrep') {
                    when { expression { env.BACKEND_CHANGED == 'true' } }
                    steps { script { runSemgrep() } }
                }

            }
        }

        stage('Code quality gate') {
            when {
                expression {
                    env.AUTOLOAD_FAILED == 'true' || env.LARASTAN_FAILED == 'true' ||
                    env.SEMGREP_FAILED == 'true'  || env.BASELINE_FAILED == 'true'
                }
            }
            steps { script { runCodeQualityGate() } }
        }

        stage('Backend tests') {
            when { expression { env.BACKEND_CHANGED == 'true' } }
            steps { script { runBackendTests() } }
        }

        stage('Tests gate') {
            when {
                expression {
                    env.BACKEND_TESTS_FAILED == 'true' && env.BACKEND_TESTS_BLOCKING == 'true'
                }
            }
            steps { script { runTestsGate() } }
        }

        stage('SonarQube analysis') {
            when { expression { env.CHANGED_PHP_FILES?.trim() } }
            steps { script { runSonarQubeAnalysis() } }
        }

        stage('SonarQube quality gate') {
            when { expression { env.SONARQUBE_RESOLVED != 'true' } }
            steps { script { runSonarQubeQualityGate() } }
        }
    }

    post {
        always {
            script {
                runPostAlways()
            }
        }
    }
}

// ── Stage implementations ────────────────────────────────────────────────────

// PR hygiene, checked through the GitHub API rather than local git: the working
// copy is a shallow depth-1 clone, so `git rev-list --count` and merge-base have
// no shared history to walk and would report nonsense.
def checkRebaseOnDevelopment() {
    postGitHubStatus('Jenkins / Rebase check', 'pending', 'Checking rebase...')

    withCredentials([usernamePassword(credentialsId: env.GITHUB_CREDENTIALS_ID, usernameVariable: 'GITHUB_USER', passwordVariable: 'GITHUB_TOKEN')]) {

        def prNum = env.CHANGE_ID ?: env.GITHUB_PR_NUMBER

        def prJson = sh(
            script: """curl -sf -H "Authorization: token \$GITHUB_TOKEN" \\
                "https://api.github.com/repos/faveosuite/${env.REPO_NAME}/pulls/${prNum}" """,
            returnStdout: true
        ).trim()
        def prHeadSha = readJSON(text: prJson).head.sha

        def compareJson = sh(
            script: """curl -sf -H "Authorization: token \$GITHUB_TOKEN" \\
                "https://api.github.com/repos/faveosuite/${env.REPO_NAME}/compare/development...${prHeadSha}" """,
            returnStdout: true
        ).trim()
        def behindBy = (readJSON(text: compareJson).behind_by ?: 0) as Integer

        if (behindBy > 0) {
            postGitHubStatus('Jenkins / Rebase check', 'failure', "PR is ${behindBy} commit(s) behind development — rebase required")
            upsertPRComment('rebase-check', """:x: **Rebase required**

This PR is **${behindBy} commit(s) behind** `development`.

Please rebase and force-push:
```
git fetch origin
git rebase origin/development
git push --force-with-lease
```""")
            error("PR branch is ${behindBy} commit(s) behind development — rebase before merging")
        }

        def commitsJson = sh(
            script: """curl -sf -H "Authorization: token \$GITHUB_TOKEN" \\
                "https://api.github.com/repos/faveosuite/${env.REPO_NAME}/pulls/${prNum}/commits?per_page=100" """,
            returnStdout: true
        ).trim()
        def commits      = readJSON text: commitsJson
        def mergeCommits = commits.findAll { (it.parents?.size() ?: 0) > 1 }

        if (mergeCommits) {
            def preview = mergeCommits.take(5)
                .collect { "- ${it.sha.take(7)} ${it.commit.message.split('\n')[0]}" }
                .join('\n')
            postGitHubStatus('Jenkins / Rebase check', 'failure', 'PR contains merge commits — rebase required')
            upsertPRComment('rebase-check', """:x: **Rebase required**

This PR contains merge commits (likely from merging `development` into the branch instead of rebasing):

${preview}

Please rebase instead:
```
git fetch origin
git rebase origin/development
git push --force-with-lease
```""")
            error('PR branch contains merge commits from development — rebase required')
        }

        def commitCount = commits.size()
        if (commitCount > 8) {
            def list = commits.collect { "- ${it.sha.take(7)} ${it.commit.message.split('\n')[0]}" }.join('\n')
            postGitHubStatus('Jenkins / Rebase check', 'failure', "PR has ${commitCount} commits — maximum is 8")
            upsertPRComment('rebase-check', """:x: **Too many commits (${commitCount}/8)**

This PR has **${commitCount} commits** but the maximum allowed is **8**.

${list}

Please squash your commits before merging:
```
git rebase -i origin/development
# mark all but the first as 'squash' or 'fixup'
git push --force-with-lease
```""")
            error("PR has ${commitCount} commits — squash to 8 or fewer before merging")
        }
    }

    env.REBASE_CHECK_RESOLVED = 'true'
    postGitHubStatus('Jenkins / Rebase check', 'success', 'Branch is cleanly rebased on development')
}

def detectChangedFiles() {
    withCredentials([usernamePassword(credentialsId: env.GITHUB_CREDENTIALS_ID, usernameVariable: 'GITHUB_USER', passwordVariable: 'GITHUB_TOKEN')]) {
        sh 'git remote set-url origin https://${GITHUB_USER}:${GITHUB_TOKEN}@github.com/faveosuite/${REPO_NAME}.git'
        retry(3) {
            sh 'git fetch --depth=1 --force origin development:refs/remotes/origin/development'
        }

        env.PR_NUMBER = env.CHANGE_ID ?: env.GITHUB_PR_NUMBER ?: ''

        def changedFiles = sh(script: 'git diff --name-only --diff-filter=d origin/development..HEAD', returnStdout: true).trim()
        echo "Changed files (branch vs development):\n${changedFiles}"

        def fileList = changedFiles ? changedFiles.split('\n') : []

        // vendor/ is COMMITTED in this repository, so a PR that bumps a dependency
        // shows thousands of third-party files in its diff. They are excluded here
        // so they reach neither the syntax check nor the analysers.
        //
        // public/ holds vendored assets (CKEditor, lb-faveo) and lang/ holds plain
        // array-return translation files.
        env.CHANGED_PHP_FILES = fileList.findAll {
            it.endsWith('.php') &&
            !it.startsWith('vendor/') && !it.startsWith('public/') &&
            !it.startsWith('node_modules/') && !it.startsWith('storage/') &&
            !it.startsWith('lang/') && !it.startsWith('resources/lang/')
        }.join(' ')

        env.CHANGED_LANG_PHP_FILES = fileList.findAll {
            it.endsWith('.php') && (it.startsWith('lang/') || it.startsWith('resources/lang/'))
        }.join(' ')

        env.BACKEND_CHANGED = (env.CHANGED_PHP_FILES?.trim() ? 'true' : 'false')

        removeLabel('Autoload Failed')
        removeLabel('Larastan Failed')
        removeLabel('Security Failed')
        removeLabel('Backend Tests Failed')
        removeLabel('SonarQube Failed')
        // 'Requires phpstan review' is NOT auto-removed — it is a sticky review
        // label. Jenkins only adds it; a maintainer removes it after approving.

        if (fileList.contains('phpstan-baseline.neon') && env.PR_NUMBER) {
            addLabel('Requires phpstan review')
            validateBaselineChanges()
        }

        if (env.BACKEND_CHANGED == 'true') {
            ['Jenkins / Autoload', 'Jenkins / Larastan', 'Jenkins / Security',
             'Jenkins / Backend Tests', 'Jenkins / SonarQube'].each { check ->
                postGitHubStatus(check, 'pending', 'Waiting...')
            }
        } else {
            env.SONARQUBE_RESOLVED = 'true'
            ['Jenkins / Autoload', 'Jenkins / Larastan', 'Jenkins / Security',
             'Jenkins / Backend Tests', 'Jenkins / SonarQube'].each { check ->
                postGitHubStatus(check, 'success', 'No PHP source files changed — skipped')
            }
        }
    }
}

def runAutoloadCheck() {
    postGitHubStatus('Jenkins / Autoload', 'pending', 'Checking autoload...')
    def rc = sh(script: 'composer dump-autoload --optimize --no-interaction', returnStatus: true)
    if (rc != 0) {
        env.AUTOLOAD_FAILED = 'true'
        postGitHubStatus('Jenkins / Autoload', 'failure', 'composer dump-autoload failed')
    } else {
        postGitHubStatus('Jenkins / Autoload', 'success', 'Autoload OK')
    }
    env.AUTOLOAD_RESOLVED = 'true'
}

// Baseline governance. phpstan-baseline.neon suppresses ~6,200 pre-existing errors;
// without this check, regenerating it is an unpoliced way to silence a failure.
def validateBaselineChanges() {
    def bt = '`'

    def prFiles = readJSON text: sh(
        script: """curl -sf -H "Authorization: token \$GITHUB_TOKEN" \\
            "https://api.github.com/repos/faveosuite/${env.REPO_NAME}/pulls/${env.PR_NUMBER}/files?per_page=100" """,
        returnStdout: true
    ).trim()

    def baselineEntry = prFiles.find { it.filename == 'phpstan-baseline.neon' }
    def patch         = baselineEntry?.patch ?: ''

    def removedPaths = patch.split('\n')
        .findAll { it =~ /^-\s+path: / }
        .collect { it.replaceAll(/^-\s+path:\s+/, '').trim() }
        .unique()
        .findAll { path -> fileExists(path) }

    def addedPaths = patch.split('\n')
        .findAll { it =~ /^\+\s+path: / }
        .collect { it.replaceAll(/^\+\s+path:\s+/, '').trim() }
        .unique()

    if (!removedPaths && !addedPaths) {
        upsertPRComment('baseline-review', """:information_source: **phpstan-baseline.neon modified**

Only formatting/structural changes detected — no entries added or removed.

> :label: **Requires phpstan review** has been added — a maintainer must review before merging.""")
        return
    }

    def commentSections = []

    if (removedPaths) {
        // Re-run PHPStan on the affected files to prove the removed errors are
        // genuinely gone, rather than taking the author's word for it.
        sh "rm -f baseline-validation.json baseline-validation-raw.txt"
        sh "php vendor/bin/phpstan analyse --memory-limit=1G --no-progress --error-format=json ${removedPaths.join(' ')} > baseline-validation.json 2>baseline-validation-raw.txt || true"

        def validationParsed  = false
        def stillFailingLines = []

        try {
            def raw       = readFile('baseline-validation.json').trim()
            def jsonStart = raw.indexOf('{')
            if (jsonStart < 0) throw new Exception('No JSON in PHPStan output')
            def result = readJSON text: raw.substring(jsonStart)
            validationParsed = true

            if (((result.totals?.file_errors ?: 0) as Integer) > 0) {
                def wsPrefix = env.WORKSPACE + '/'
                (result.files ?: [:]).each { absPath, fileData ->
                    def relPath = absPath.startsWith(wsPrefix) ? absPath.substring(wsPrefix.length()) : absPath
                    fileData.messages?.each { msg ->
                        stillFailingLines << "- ${bt}${relPath}:${msg.line}${bt} — ${msg.message}"
                    }
                }
            }
        } catch (e) {
            echo "Could not parse PHPStan validation output: ${e.message}"
        }

        if (!validationParsed) {
            env.BASELINE_FAILED = 'true'
            postGitHubStatus('Jenkins / Baseline review', 'failure', 'Baseline validation — PHPStan produced no output')
            commentSections << """:warning: **Removed entries — PHPStan validation could not run**

PHPStan produced no output when checking the files affected by the removed baseline entries, so the removal cannot be verified automatically.

Please re-run the pipeline or confirm manually that the errors are gone before merging."""
        } else if (stillFailingLines) {
            env.BASELINE_FAILED = 'true'
            postGitHubStatus('Jenkins / Baseline review', 'failure', "Baseline removal invalid — ${stillFailingLines.size()} error(s) still present")
            commentSections << """:x: **Removed entries — errors still exist in the code**

These entries were removed from the baseline, but PHPStan still reports the errors:

${stillFailingLines.join('\n')}

**Fix options:**
1. Fix the errors in the source and then remove the baseline entries, or
2. Restore the baseline entries if the errors cannot be fixed right now."""
        } else {
            postGitHubStatus('Jenkins / Baseline review', 'success', 'Removed baseline entries validated')
            commentSections << """:white_check_mark: **Removed entries — validated**

PHPStan confirmed the removed errors no longer exist in:

${removedPaths.collect { "- ${bt}${it}${bt}" }.join('\n')}"""
        }
    }

    if (addedPaths) {
        commentSections << """:eyes: **Added entries — suppressing new errors**

New baseline suppressions were added for:

${addedPaths.collect { "- ${bt}${it}${bt}" }.join('\n')}

A maintainer must confirm these suppressions are intentional and that the underlying errors cannot be fixed in the code."""
    }

    upsertPRComment('baseline-review', """**phpstan-baseline.neon modified**

${commentSections.join('\n\n---\n\n')}

> :label: **Requires phpstan review** has been added — a maintainer must approve before merging.""")
}

def runLarastan() {
    postGitHubStatus('Jenkins / Larastan', 'pending', 'Larastan running...')

    // Drop PHPStan's result cache so a stale cache cannot mask a new error.
    sh 'rm -rf /tmp/phpstan'

    // ── 1. Syntax check ──────────────────────────────────────────────────────
    // Covers changed PHP including lang/ files, which PHPStan skips.
    //
    // *.blade.php is deliberately EXCLUDED. A Blade template is not standalone
    // PHP: directives such as @continue / @break compile to statements that are
    // only valid inside the loop Blade generates, so `php -l` reports a fatal
    // error on a perfectly valid template. This repository has 326 Blade files,
    // so without this exclusion nearly every PR would fail here.
    def allChangedPhp = [env.CHANGED_PHP_FILES?.trim(), env.CHANGED_LANG_PHP_FILES?.trim()]
        .findAll { it }
        .join(' ')
        .trim()

    def syntaxTargets = allChangedPhp
        ? allChangedPhp.split(' ').toList().findAll { !it.endsWith('.blade.php') }.join(' ')
        : ''

    if (syntaxTargets) {
        def syntaxOut = sh(
            script: "for f in ${syntaxTargets}; do php -l \"\$f\" 2>&1; done; exit 0",
            returnStdout: true
        ).trim()
        def syntaxErrors = syntaxOut.split('\n').findAll { it.contains('Parse error') || it.contains('Fatal error') }
        if (syntaxErrors) {
            echo "PHP syntax errors found:\n${syntaxErrors.join('\n')}"
            writeFile file: 'larastan-report.txt', text: syntaxErrors.collect { "- ${it}" }.join('\n')
            env.LARASTAN_FAILED   = 'true'
            env.LARASTAN_RESOLVED = 'true'
            postGitHubStatus('Jenkins / Larastan', 'failure', "${syntaxErrors.size()} PHP syntax error(s)")
            return
        }
    }

    if (!env.CHANGED_PHP_FILES?.trim()) {
        echo 'No PHP files for Larastan (only lang files changed — syntax check passed).'
        env.LARASTAN_RESOLVED = 'true'
        postGitHubStatus('Jenkins / Larastan', 'success', 'No PHP class files changed — syntax OK')
        return
    }

    // ── 2. Narrow to files PHPStan can analyse ───────────────────────────────
    // These are excluded in phpstan.neon too, but they must ALSO be filtered out
    // of the argument list: PHPStan given nothing but excluded paths exits with
    // "No files found to analyse" and emits no JSON at all, which the retry loop
    // below would then misreport as an infrastructure failure.
    // Only files inside phpstan.neon's `paths` are analysed. phpstan-baseline.neon
    // was generated over app/, database/, routes/ and config/ only, so a file from
    // anywhere else (bootstrap/, artisan, …) carries NO baseline coverage — a PR
    // that merely touched it would be blocked by errors it did not introduce.
    def phpStanFiles = env.CHANGED_PHP_FILES.trim().split(' ').toList()
        .findAll { f ->
            (f.startsWith('app/') || f.startsWith('database/') ||
             f.startsWith('routes/') || f.startsWith('config/')) &&
            !f.startsWith('tests/') && !f.contains('/tests/') && !f.contains('/Tests/') &&
            !f.endsWith('Test.php') &&
            !f.endsWith('.blade.php') && !f.startsWith('resources/views/')
        }
        .join(' ')

    if (!phpStanFiles?.trim()) {
        echo 'Only views/tests/scripts changed — PHPStan skipped (syntax check already passed).'
        env.LARASTAN_RESOLVED = 'true'
        postGitHubStatus('Jenkins / Larastan', 'success', 'No analysable PHP files changed — syntax OK')
        return
    }

    // ── 3. Analyse, with one retry ───────────────────────────────────────────
    def changedLines = readJSON text: sh(
        script: 'php scripts/get-changed-lines.php . origin/development',
        returnStdout: true
    ).trim()

    def larastanErrors = []
    def phpstanParsed  = false

    for (int attempt = 1; attempt <= 2; attempt++) {
        sh """
            rm -f larastan-report.json larastan-raw.txt
            php vendor/bin/phpstan analyse --memory-limit=1G --no-progress --error-format=json ${phpStanFiles} > larastan-report.json 2>larastan-raw.txt || true
        """
        try {
            def raw       = readFile('larastan-report.json').trim()
            def jsonStart = raw.indexOf('{')
            if (jsonStart < 0) throw new Exception('No JSON found in phpstan output')
            def larastanJson = readJSON text: raw.substring(jsonStart)

            // 0 errors means everything fell inside phpstan-baseline.neon.
            if (((larastanJson.totals?.file_errors ?: 0) as Integer) == 0) {
                env.LARASTAN_RESOLVED = 'true'
                postGitHubStatus('Jenkins / Larastan', 'success', 'Larastan passed')
                return
            }

            def wsPrefix        = env.WORKSPACE + '/'
            def normalizedFiles = [:]
            (larastanJson.files ?: [:]).each { absPath, fileData ->
                def relPath = absPath.startsWith(wsPrefix) ? absPath.substring(wsPrefix.length()) : absPath
                normalizedFiles[relPath] = fileData
            }
            larastanErrors = filterLarastanMessages(changedLines, normalizedFiles)
            phpstanParsed  = true
            break
        } catch (e) {
            def rawLog = fileExists('larastan-raw.txt') ? readFile('larastan-raw.txt').trim() : ''
            echo "PHPStan attempt ${attempt}/2 failed: ${e.message}" + (rawLog ? "\nstderr: ${rawLog}" : '')
        }
    }

    // No usable output is an INFRASTRUCTURE failure, not the author's fault.
    if (!phpstanParsed) {
        def rawLog = fileExists('larastan-raw.txt') ? readFile('larastan-raw.txt').trim() : 'no stderr captured'
        env.LARASTAN_RESOLVED = 'true'
        postGitHubStatus('Jenkins / Larastan', 'failure', 'Larastan infra error — PHPStan produced no valid JSON')
        error("PHPStan failed to produce valid JSON after 2 attempts (infra error). stderr: ${rawLog.take(500)}")
    }

    if (larastanErrors) {
        writeFile file: 'larastan-report.txt', text: larastanErrors.join('\n')
        env.LARASTAN_FAILED   = 'true'
        env.LARASTAN_RESOLVED = 'true'
        postGitHubStatus('Jenkins / Larastan', 'failure', "${larastanErrors.size()} type error(s) on changed lines")
    } else {
        // Errors exist elsewhere but none on lines this PR changed — pass.
        env.LARASTAN_RESOLVED = 'true'
        postGitHubStatus('Jenkins / Larastan', 'success', 'Larastan passed — no errors on changed lines')
    }
}

def runSemgrep() {
    postGitHubStatus('Jenkins / Security', 'pending', 'Semgrep security scan running...')

    // Blade templates ARE scanned here, unlike in Larastan: Semgrep parses
    // *.blade.php without complaint (verified on all 326 of them, zero parse
    // errors), and in a Blade application the templates are where output-escaping
    // bugs live. Tests are excluded — a hardcoded credential in a fixture is not a
    // production vulnerability.
    def sourceFiles = env.CHANGED_PHP_FILES?.trim()
        ? env.CHANGED_PHP_FILES.trim().split(' ').toList()
            .findAll { !it.toLowerCase().startsWith('tests/') && !it.contains('/tests/') }
        : []

    if (sourceFiles.isEmpty()) {
        env.SEMGREP_RESOLVED = 'true'
        postGitHubStatus('Jenkins / Security', 'success', 'No source PHP files to scan')
        return
    }

    // Jenkins runs a non-interactive shell where /usr/local/bin is often missing
    // from PATH, so probe the usual install locations before falling back.
    def semgrepBin = sh(script: '''
        for p in /usr/local/bin/semgrep /usr/bin/semgrep /home/jenkins/.local/bin/semgrep; do
            [ -x "$p" ] && echo "$p" && exit 0
        done
        command -v semgrep 2>/dev/null || true
    ''', returnStdout: true).trim()

    if (!semgrepBin) {
        echo 'Semgrep not found on this agent — skipping security scan'
        env.SEMGREP_RESOLVED = 'true'
        postGitHubStatus('Jenkins / Security', 'success', 'Semgrep not installed — skipped')
        return
    }
    echo "Semgrep found at: ${semgrepBin}"

    // The p/* rulesets are fetched from the Semgrep registry at scan time, so the
    // agent needs outbound network access. --metrics=off keeps findings off
    // Semgrep's telemetry.
    sh """
        rm -f semgrep-results.json
        ${semgrepBin} --config=p/php --config=p/owasp-top-ten --config=p/secrets --config=p/security-audit \\
            --metrics=off --json --output=semgrep-results.json ${sourceFiles.join(' ')} || true
    """

    // A missing result file means Semgrep did not run (network, crash, bad rules).
    // Treating that as "no issues found" would be a silent pass on a security
    // gate, so it is reported as an infrastructure failure instead.
    if (!fileExists('semgrep-results.json')) {
        env.SEMGREP_RESOLVED = 'true'
        postGitHubStatus('Jenkins / Security', 'failure', 'Semgrep infra error — no results file produced')
        error('Semgrep produced no output file (infra error) — see the build log')
    }

    def semgrepOutput = readJSON file: 'semgrep-results.json'

    def changedLines = readJSON text: sh(
        script: 'php scripts/get-changed-lines.php . origin/development',
        returnStdout: true
    ).trim()

    def relevant = filterSemgrepFindings(changedLines, semgrepOutput.results)

    if (relevant.size() > 0) {
        writeFile file: 'semgrep-report.txt', text: formatSemgrepReport(relevant)
        env.SEMGREP_FAILED   = 'true'
        env.SEMGREP_RESOLVED = 'true'
        postGitHubStatus('Jenkins / Security', 'failure', "${relevant.size()} security issue(s) found")
    } else {
        env.SEMGREP_RESOLVED = 'true'
        postGitHubStatus('Jenkins / Security', 'success', 'No security issues in changed files')
    }
}

def runCodeQualityGate() {
    def sections = []

    if (env.AUTOLOAD_FAILED == 'true') {
        addLabel('Autoload Failed')
        sections << "<details>\n<summary><strong>Autoload</strong></summary>\n\n`composer dump-autoload --optimize` failed. See the [build log](${env.BUILD_URL}console).\n\n</details>"
    }
    if (env.BASELINE_FAILED == 'true') {
        addLabel('Requires phpstan review')
        sections << "<details>\n<summary><strong>phpstan-baseline.neon &mdash; invalid change</strong></summary>\n\nEntries were removed from the baseline but PHPStan still reports those errors, or the removal could not be verified. See the baseline-review comment on this PR.\n\n</details>"
    }
    if (env.LARASTAN_FAILED == 'true') {
        addLabel('Larastan Failed')
        def raw   = fileExists('larastan-report.txt') ? readFile('larastan-report.txt').trim() : ''
        def count = raw ? raw.split('\n').toList().findAll { it.startsWith('- ') }.size() : 0
        if (raw) archiveArtifacts artifacts: 'larastan-report.txt', allowEmptyArchive: true
        def body = raw.take(3000)
        def more = raw.length() > 3000
            ? "\n\n> _(${count} error(s) total — partial list shown. [Download full report](${env.BUILD_URL}artifact/larastan-report.txt))_"
            : "\n\n> _[Download full report](${env.BUILD_URL}artifact/larastan-report.txt)_"
        sections << "<details>\n<summary><strong>Larastan &mdash; ${count} type error(s)</strong></summary>\n\n${body}${more}\n\n</details>"
    }
    if (env.SEMGREP_FAILED == 'true') {
        addLabel('Security Failed')
        def raw   = fileExists('semgrep-report.txt') ? readFile('semgrep-report.txt').trim() : ''
        def count = raw ? raw.split('\n').toList().findAll { it.startsWith('- ') }.size() : 0
        if (raw) archiveArtifacts artifacts: 'semgrep-report.txt', allowEmptyArchive: true
        def more = "\n\n> _[Download full report](${env.BUILD_URL}artifact/semgrep-report.txt)_"
        sections << "<details>\n<summary><strong>Security (Semgrep) &mdash; ${count} finding(s)</strong></summary>\n\n${raw.take(3000)}${more}\n\n</details>"
    }

    env.FAILURE_COMMENT_POSTED = 'true'
    postNewPRComment("### :x: Code Quality Failed\n\n${sections.join('\n\n')}")
    error('Code quality checks failed')
}

def runBackendTests() {
    postGitHubStatus('Jenkins / Backend Tests', 'pending', 'Backend tests running...')

    sh 'php artisan optimize:clear || true'

    withCredentials([usernamePassword(credentialsId: env.MYSQL_CREDENTIALS_ID, usernameVariable: 'DB_USER', passwordVariable: 'DB_PASS')]) {
        env.DB_NAME = "faveo_community_${env.BUILD_NUMBER}_${env.PR_NUMBER}"
        sh """
            mysql -u "\$DB_USER" -p"\$DB_PASS" -e "DROP DATABASE IF EXISTS ${env.DB_NAME}; CREATE DATABASE ${env.DB_NAME};" 2>/dev/null || true
            php artisan testing-setup --username="\$DB_USER" --password="\$DB_PASS" --database="${env.DB_NAME}"
        """

        // Coverage feeds SonarQube for visibility only — it is NOT a gate.
        // NOTE: phpunit.xml still uses the PHPUnit 9 <filter><whitelist> syntax,
        // which PHPUnit 12 ignores ("No filter is configured, code coverage will
        // not be processed"). Until it is converted to <source><include>, no
        // clover.xml is produced and Sonar simply reports no coverage.
        sh 'mkdir -p storage/sonarqube'
        def exitCode = sh(script: buildTestScript(env.DB_NAME), returnStatus: true)

        sh "mysql -u \"\$DB_USER\" -p\"\$DB_PASS\" -e \"DROP DATABASE IF EXISTS ${env.DB_NAME};\" 2>/dev/null || true"
        junit allowEmptyResults: true, testResults: 'backend-test-results.xml'

        if (exitCode != 0) {
            def failedTests = sh(script: '''
                php -r '
                if (!file_exists("backend-test-results.xml")) exit;
                $xml = simplexml_load_file("backend-test-results.xml");
                $cases = $xml->xpath("//testcase[failure or error]");
                if (!count($cases)) exit;
                echo count($cases) . " test(s) failed:\n\n";
                foreach ($cases as $tc) {
                    $node = $tc->failure[0] ?? $tc->error[0];
                    $msg  = substr(preg_replace("/\n.*/s", "", trim((string)$node)), 0, 150);
                    echo "- `" . (string)$tc["classname"] . "::" . (string)$tc["name"] . "`\n  " . $msg . "\n";
                }
                ' 2>/dev/null || true
            ''', returnStdout: true).trim()

            if (!failedTests) {
                failedTests = sh(
                    script: "sed 's/\\x1b\\[[0-9;]*[mGKHF]//g' backend-test-report.txt | tail -50 || true",
                    returnStdout: true
                ).trim()
            }

            env.BACKEND_TESTS_FAILED         = 'true'
            env.BACKEND_TESTS_FAILURE_DETAIL = failedTests.take(3000)
        }
    }

    env.BACKEND_TESTS_RESOLVED = 'true'

    if (env.BACKEND_TESTS_FAILED != 'true') {
        postGitHubStatus('Jenkins / Backend Tests', 'success', 'All backend tests passed')
        return
    }

    // The status still shows red — that is the honest result — but the build only
    // fails when BACKEND_TESTS_BLOCKING is 'true'.
    def blocking = env.BACKEND_TESTS_BLOCKING == 'true'
    postGitHubStatus('Jenkins / Backend Tests', 'failure',
        blocking ? 'Backend tests failed' : 'Backend tests failed (non-blocking)')

    if (!blocking) {
        echo 'Backend tests failed, but BACKEND_TESTS_BLOCKING is false — not failing the build.'
        upsertPRComment('tests', """### :warning: Backend Tests Failed (non-blocking)

${env.BACKEND_TESTS_FAILURE_DETAIL ?: 'No details captured.'}

> This check is currently **reporting only** — it does not block the merge while the
> existing test suite is being stabilised. [View full output](${env.BUILD_URL}console)""")
    }
}

def runTestsGate() {
    addLabel('Backend Tests Failed')
    env.FAILURE_COMMENT_POSTED = 'true'
    upsertPRComment('tests', """### :x: Backend Tests Failed

${env.BACKEND_TESTS_FAILURE_DETAIL ?: 'No details captured — see full output.'}

[View full output](${env.BUILD_URL}console)""")
    error('Tests failed')
}

def runSonarQubeAnalysis() {
    postGitHubStatus('Jenkins / SonarQube', 'pending', 'SonarQube analysis running...')
    def projectKey  = sonarProjectKey()
    def projectName = "Faveo Community PR #${env.PR_NUMBER ?: env.BUILD_NUMBER}"

    withCredentials([string(credentialsId: 'sonar-admin-token', variable: 'SONAR_ADMIN_TOKEN')]) {
        sh """
            curl -s -X POST "${env.SONAR_HOST_URL}/api/projects/delete" -u "\$SONAR_ADMIN_TOKEN:" -d "project=${projectKey}" || true
            curl -s -X POST "${env.SONAR_HOST_URL}/api/projects/create" \\
                 -u "\$SONAR_ADMIN_TOKEN:" \\
                 -H "Content-Type: application/x-www-form-urlencoded" \\
                 -d "project=${projectKey}&name=${projectName}&visibility=private&newCodeDefinitionType=PREVIOUS_VERSION"
        """
    }

    // Only the PR's own files are ingested. Without this, a brand-new project has
    // no previous version to diff against and SonarQube treats the whole codebase
    // as new code — which would fail the gate on every PR.
    def prInclusions = env.CHANGED_PHP_FILES.trim().split(' ').toList()
        .findAll { !it.toLowerCase().startsWith('tests/') }
        .join(',')

    if (!prInclusions) {
        env.SONARQUBE_RESOLVED = 'true'
        postGitHubStatus('Jenkins / SonarQube', 'success', 'Only test files changed — skipped')
        return
    }

    withSonarQubeEnv('local-sonar') {
        withCredentials([string(credentialsId: 'sonar-admin-token', variable: 'SONAR_ADMIN_TOKEN')]) {
            sh """
                sonar-scanner \\
                  -Dsonar.projectKey=${projectKey} \\
                  -Dsonar.sources=. \\
                  -Dsonar.token="\$SONAR_ADMIN_TOKEN" \\
                  -Dsonar.inclusions=${prInclusions} \\
                  -Dsonar.php.coverage.reportPaths=storage/sonarqube/clover.xml \\
                  -Dsonar.sourceEncoding=UTF-8
            """
        }
    }
}

def runSonarQubeQualityGate() {
    def projectKey = sonarProjectKey()
    try {
        timeout(time: 5, unit: 'MINUTES') { waitForQualityGate(abortPipeline: false) }
    } catch (e) {
        echo 'SonarQube webhook not received in time — continuing with direct API check'
    }

    def changedLines = readJSON text: sh(
        script: 'php scripts/get-changed-lines.php . origin/development',
        returnStdout: true
    ).trim()

    withCredentials([string(credentialsId: 'sonar-admin-token', variable: 'SONAR_TOKEN')]) {

        def blockingUrl  = "${env.SONAR_HOST_URL}/api/issues/search?componentKeys=${projectKey}&resolved=false&types=BUG,VULNERABILITY&severities=BLOCKER,CRITICAL&ps=500"
        def blockingJson = readJSON text: sh(script: "curl -s -u \"\$SONAR_TOKEN:\" \"${blockingUrl}\"", returnStdout: true).trim()

        def relevantBlocking = (blockingJson.issues ?: []).findAll { issue ->
            def file    = issue.component.tokenize(':').last()
            def lineNum = issue.line as Integer
            if (!lineNum) return true
            def lines = changedLines[file] as List
            return lines != null && lines.contains(lineNum)
        }

        def warningUrl  = "${env.SONAR_HOST_URL}/api/issues/search?componentKeys=${projectKey}&resolved=false&types=BUG,VULNERABILITY,CODE_SMELL&severities=MAJOR,MINOR,INFO&ps=500"
        def warningJson = readJSON text: sh(script: "curl -s -u \"\$SONAR_TOKEN:\" \"${warningUrl}\"", returnStdout: true).trim()

        def relevantWarning = (warningJson.issues ?: []).findAll { issue ->
            def file    = issue.component.tokenize(':').last()
            def lineNum = issue.line as Integer
            if (!lineNum) return false
            def lines = changedLines[file] as List
            return lines != null && lines.contains(lineNum)
        }

        if (relevantWarning.size() > 0) {
            def warnBody = relevantWarning.collect { issue ->
                "- **${issue.severity}** — ${issue.message} in `${issue.component.tokenize(':').last()}` line ${issue.line ?: '?'}"
            }.join('\n')
            writeFile file: 'sonar-warn-report.txt', text: warnBody
            archiveArtifacts artifacts: 'sonar-warn-report.txt', allowEmptyArchive: true
            upsertPRComment('sonar-warn', """### :warning: SonarQube Warnings (non-blocking)

<details>
<summary><strong>SonarQube Warnings &mdash; ${relevantWarning.size()} issue(s)</strong></summary>

${warnBody.take(3000)}

> _[Download full report](${env.BUILD_URL}artifact/sonar-warn-report.txt)_

</details>

> These are MAJOR / MINOR / INFO issues. The build continues — please review before merging.""")
            echo "SonarQube: ${relevantWarning.size()} warning(s) posted — build continues"
        }

        if (relevantBlocking.size() > 0) {
            def blockBody = relevantBlocking.collect { issue ->
                "- **${issue.severity}** — ${issue.message} in `${issue.component.tokenize(':').last()}` line ${issue.line ?: '?'}"
            }.join('\n')
            writeFile file: 'sonar-block-report.txt', text: blockBody
            archiveArtifacts artifacts: 'sonar-block-report.txt', allowEmptyArchive: true
            addLabel('SonarQube Failed')
            env.SONARQUBE_RESOLVED     = 'true'
            env.FAILURE_COMMENT_POSTED = 'true'
            postGitHubStatus('Jenkins / SonarQube', 'failure', "${relevantBlocking.size()} blocking issue(s) found in changed lines")
            postNewPRComment("""### :x: SonarQube Blocking Issues Found

<details>
<summary><strong>SonarQube Issues &mdash; ${relevantBlocking.size()} blocking issue(s)</strong></summary>

${blockBody.take(3000)}

> _[Download full report](${env.BUILD_URL}artifact/sonar-block-report.txt)_

</details>

[View full report](${env.SONAR_HOST_URL}/project/issues?id=${projectKey})""")
            error('SonarQube found blocking issues in changed lines')
        }

        env.SONARQUBE_RESOLVED = 'true'
        postGitHubStatus('Jenkins / SonarQube', 'success', 'No blocking issues found in changed lines')

        // Gate passed — drop the throwaway project so SonarQube does not
        // accumulate one dead project per pull request.
        sh """curl -s -X POST -u "\$SONAR_TOKEN:" "${env.SONAR_HOST_URL}/api/projects/delete" -d "project=${projectKey}" > /dev/null || true"""
    }
}

def runPostAlways() {
    // No check may be left stuck on 'pending' forever.
    if (!env.REBASE_CHECK_RESOLVED) {
        postGitHubStatus('Jenkins / Rebase check', 'failure', 'Did not run — build failed earlier')
    }

    if (!env.BACKEND_CHANGED) {
        ['Jenkins / Autoload', 'Jenkins / Larastan', 'Jenkins / Security',
         'Jenkins / Backend Tests', 'Jenkins / SonarQube'].each { check ->
            postGitHubStatus(check, 'failure', 'Build failed before checks could start')
        }
    } else if (env.BACKEND_CHANGED == 'true') {
        if (!env.AUTOLOAD_RESOLVED)      postGitHubStatus('Jenkins / Autoload', 'failure', 'Did not run — build failed earlier')
        if (!env.LARASTAN_RESOLVED)      postGitHubStatus('Jenkins / Larastan', 'failure', 'Did not run — build failed earlier')
        if (!env.SEMGREP_RESOLVED)       postGitHubStatus('Jenkins / Security', 'failure', 'Did not run — build failed earlier')
        if (!env.BACKEND_TESTS_RESOLVED) postGitHubStatus('Jenkins / Backend Tests', 'failure', 'Did not run — build failed earlier')
        if (!env.SONARQUBE_RESOLVED)     postGitHubStatus('Jenkins / SonarQube', 'failure', 'Did not run — build failed earlier')
    }

    // The `junit` step marks a build UNSTABLE when it publishes failing tests.
    // While backend tests are non-blocking that is an expected, already-reported
    // outcome — not an unexplained failure — so it is treated as a pass here.
    def result           = currentBuild.currentResult
    def testsNonBlocking = env.BACKEND_TESTS_BLOCKING != 'true'
    def unstableFromTests = (result == 'UNSTABLE' && testsNonBlocking)
    def buildOk          = (result == 'SUCCESS') || unstableFromTests

    if (!buildOk && result != 'ABORTED' && env.FAILURE_COMMENT_POSTED != 'true') {
        try {
            upsertPRComment('unexpected-failure', """:x: **Build failed unexpectedly**

The build did not reach a normal failure gate (code quality, tests, SonarQube). An unhandled error aborted the pipeline mid-stage.

**[View the build log](${env.BUILD_URL}console)** to see what went wrong.

> If this is an infrastructure issue (network, disk, OOM), push an empty commit to re-trigger: `git commit --allow-empty -m "retry ci" && git push`""")
        } catch (ignored) {}
    }

    // NOTE: currentBuild.currentResult, NOT currentBuild.result. The latter is null
    // on a successful build, which would report every green build as failed.
    //
    // The PR is never closed automatically. A failed check reports the failure; it
    // does not discard a contributor's work.
    if (env.GIT_COMMIT) {
        postGitHubStatus('continuous-integration/jenkins/pr-head',
                         buildOk ? 'success' : 'failure',
                         buildOk ? 'All checks passed' : 'Build failed')
    }

    if (env.DB_NAME) {
        withCredentials([usernamePassword(credentialsId: env.MYSQL_CREDENTIALS_ID, usernameVariable: 'DB_USER', passwordVariable: 'DB_PASS')]) {
            sh "mysql -u \"\$DB_USER\" -p\"\$DB_PASS\" -e \"DROP DATABASE IF EXISTS ${env.DB_NAME};\" 2>/dev/null || true"
        }
    }

    cleanWs()
}

def sonarProjectKey() { return "faveo-community-${env.PR_NUMBER ?: env.BUILD_NUMBER}" }

// ── GitHub API helpers ───────────────────────────────────────────────────────

def notifyInfraFailure(String stageName, String message) {
    def prNum = env.CHANGE_ID ?: env.GITHUB_PR_NUMBER
    if (!env.GIT_COMMIT && prNum) {
        try {
            withCredentials([usernamePassword(credentialsId: env.GITHUB_CREDENTIALS_ID, usernameVariable: 'GITHUB_USER', passwordVariable: 'GITHUB_TOKEN')]) {
                def sha = sh(
                    script: """curl -sf -H "Authorization: token \$GITHUB_TOKEN" "https://api.github.com/repos/faveosuite/${env.REPO_NAME}/pulls/${prNum}" | jq -r '.head.sha // empty'""",
                    returnStdout: true
                ).trim()
                if (sha) env.GIT_COMMIT = sha
            }
        } catch (ignored) {}
    }

    ['Jenkins / Rebase check', 'Jenkins / Autoload', 'Jenkins / Larastan',
     'Jenkins / Security', 'Jenkins / Backend Tests', 'Jenkins / SonarQube'].each { check ->
        postGitHubStatus(check, 'error', "Infrastructure error in '${stageName}' stage")
    }

    try {
        env.FAILURE_COMMENT_POSTED = 'true'
        upsertPRComment('infra', """:x: **Jenkins Infrastructure Error — ${stageName} stage failed**

**Error:** ${message}

**What to do:**
1. **Re-trigger this build** — push an empty commit (`git commit --allow-empty -m "retry ci"`) or ask a reviewer to re-run Jenkins.
2. Review the [build log](${env.BUILD_URL}console) for details.
3. Contact the DevOps team if the issue persists.""")
    } catch (ignored) {}
}

def postGitHubStatus(String context, String state, String description) {
    if (!env.REPO_NAME || !env.GIT_COMMIT) return
    if (currentBuild.result == 'ABORTED') return
    withCredentials([usernamePassword(credentialsId: env.GITHUB_CREDENTIALS_ID, usernameVariable: 'GITHUB_USER', passwordVariable: 'GITHUB_TOKEN')]) {
        sh """
            curl -s -u \${GITHUB_USER}:\${GITHUB_TOKEN} \\
                 -d '{"state":"${state}","target_url":"${env.BUILD_URL}","description":"${description}","context":"${context}"}' \\
                 https://api.github.com/repos/faveosuite/${env.REPO_NAME}/statuses/${env.GIT_COMMIT} > /dev/null
        """
    }
}

def postNewPRComment(String body) {
    if (currentBuild.result == 'ABORTED') return
    def prNum = env.PR_NUMBER ?: env.CHANGE_ID ?: env.GITHUB_PR_NUMBER
    if (!prNum) return
    withCredentials([usernamePassword(credentialsId: env.GITHUB_CREDENTIALS_ID, usernameVariable: 'GITHUB_USER', passwordVariable: 'GITHUB_TOKEN')]) {
        writeFile file: 'pr-comment.json', text: groovy.json.JsonOutput.toJson([body: body])
        sh """
            curl -s -X POST \\
            -H "Authorization: token \$GITHUB_TOKEN" \\
            -H "Content-Type: application/json" \\
            -d @pr-comment.json \\
            "https://api.github.com/repos/faveosuite/${env.REPO_NAME}/issues/${prNum}/comments" > /dev/null
        """
    }
}

def upsertPRComment(String marker, String body) {
    if (currentBuild.result == 'ABORTED') return
    def prNum = env.PR_NUMBER ?: env.CHANGE_ID ?: env.GITHUB_PR_NUMBER
    if (!prNum) return
    def tag = "<!-- jenkins:${marker} -->"
    withCredentials([usernamePassword(credentialsId: env.GITHUB_CREDENTIALS_ID, usernameVariable: 'GITHUB_USER', passwordVariable: 'GITHUB_TOKEN')]) {
        def existingId = sh(
            script: """
                curl -s -H "Authorization: token \$GITHUB_TOKEN" \\
                     "https://api.github.com/repos/faveosuite/${env.REPO_NAME}/issues/${prNum}/comments?per_page=100" \\
                | jq -r '.[] | select(.body | contains("${tag}")) | .id' | head -1
            """,
            returnStdout: true
        ).trim()

        writeFile file: 'pr-comment.json', text: groovy.json.JsonOutput.toJson([body: "${tag}\n${body}"])

        if (existingId) {
            sh """
                curl -s -X PATCH \\
                -H "Authorization: token \$GITHUB_TOKEN" \\
                -H "Content-Type: application/json" \\
                -d @pr-comment.json \\
                "https://api.github.com/repos/faveosuite/${env.REPO_NAME}/issues/comments/${existingId}" > /dev/null
            """
        } else {
            sh """
                curl -s -X POST \\
                -H "Authorization: token \$GITHUB_TOKEN" \\
                -H "Content-Type: application/json" \\
                -d @pr-comment.json \\
                "https://api.github.com/repos/faveosuite/${env.REPO_NAME}/issues/${prNum}/comments" > /dev/null
            """
        }
    }
}

def addLabel(String label) {
    if (!env.PR_NUMBER) return
    withCredentials([usernamePassword(credentialsId: env.GITHUB_CREDENTIALS_ID, usernameVariable: 'GITHUB_USER', passwordVariable: 'GITHUB_TOKEN')]) {
        sh """
            curl -s -X POST \\
            -H "Authorization: token \${GITHUB_TOKEN}" \\
            -H "Content-Type: application/json" \\
            -d '{"labels":["${label}"]}' \\
            https://api.github.com/repos/faveosuite/${env.REPO_NAME}/issues/${env.PR_NUMBER}/labels > /dev/null
        """
    }
}

def removeLabel(String label) {
    if (!env.PR_NUMBER) return
    def encoded = label.replace(' ', '%20')
    withCredentials([usernamePassword(credentialsId: env.GITHUB_CREDENTIALS_ID, usernameVariable: 'GITHUB_USER', passwordVariable: 'GITHUB_TOKEN')]) {
        sh """
            curl -s -X DELETE \\
            -H "Authorization: token \${GITHUB_TOKEN}" \\
            https://api.github.com/repos/faveosuite/${env.REPO_NAME}/issues/${env.PR_NUMBER}/labels/${encoded} > /dev/null || true
        """
    }
}

// ── Pure helpers ─────────────────────────────────────────────────────────────

@NonCPS
def buildTestScript(String dbName) {
    return """#!/bin/bash
set -o pipefail
DB_USERNAME="\$DB_USER" DB_PASSWORD="\$DB_PASS" DB_DATABASE="${dbName}" \\
SESSION_DRIVER=array QUEUE_CONNECTION=sync \\
stdbuf -i0 -o0 -e0 php -d pcov.enabled=1 -d memory_limit=512M -d output_buffering=Off -d implicit_flush=On \\
vendor/bin/phpunit --colors=always --testdox --testsuite unit \\
--coverage-clover=storage/sonarqube/clover.xml --log-junit=backend-test-results.xml \\
2>&1 | stdbuf -i0 -o0 tee backend-test-report.txt"""
}

@NonCPS
def filterLarastanMessages(def changedLines, def larastanFiles) {
    def errors = []
    (larastanFiles ?: [:]).each { filePath, fileData ->
        def fileChangedLines = changedLines[filePath] as List
        if (fileChangedLines == null) return
        (fileData.messages ?: []).each { error ->
            def lineNum = error.line as Integer
            if (!lineNum || !fileChangedLines.contains(lineNum)) return
            def safeMsg = error.message.replaceAll(/#(\d+)/, '&#35;$1')
            errors << "- ${safeMsg} → `${filePath}` line ${lineNum}"
        }
    }
    return errors
}

// Matched by FILE, not by line — deliberately. A security finding is usually a
// data-flow issue: the tainted value enters in one place and is used in another,
// so the reported line is often not a line the author edited. Line matching would
// hide exactly the findings worth blocking on.
@NonCPS
def filterSemgrepFindings(def changedLines, def results) {
    def relevant = []
    for (def f : (results ?: [])) {
        def filePath = f.path.startsWith('./') ? f.path.substring(2) : f.path
        if (changedLines.containsKey(filePath)) relevant.add(f)
    }
    return relevant
}

@NonCPS
def formatSemgrepReport(def relevant) {
    return relevant.collect { f ->
        def filePath = f.path.startsWith('./') ? f.path.substring(2) : f.path
        "- **${f.extra.severity}** — ${f.extra.message} in `${filePath}` line ${f.start.line} [${f.check_id}]"
    }.join('\n')
}
