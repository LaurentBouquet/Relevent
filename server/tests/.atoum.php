<?php
/*
 * Created by PhpStorm.
 * User: Paul
 * Date: 12/07/2017
 * Time: 18:45
 */


use
    mageekguy\atoum\reports,
    mageekguy\atoum\reports\telemetry,
    mageekguy\atoum\writers\std;

$runner->addTestsFromDirectory(__DIR__ . '/Database');

$script->addDefaultReport();

if (file_exists(__DIR__ . '/vendor/autoload.php') === true)
{
    require_once __DIR__ . '/vendor/autoload.php';
}

if (class_exists('mageekguy\atoum\reports\telemetry') === true)
{
    $telemetry = new telemetry();
    $telemetry->readProjectNameFromComposerJson(__DIR__ . '/composer.json');
    $telemetry->addWriter(new std\out());
    $runner->addReport($telemetry);
}

$sources = 'classes';
$token = getenv('COVERALLS_REPO_TOKEN') ?: null;
$coverallsReport = new reports\asynchronous\coveralls($sources, $token);

$defaultFinder = $coverallsReport->getBranchFinder();
$coverallsReport
    ->setBranchFinder(function() use ($defaultFinder) {
        if (($branch = getenv('TRAVIS_BRANCH')) === false)
        {
            $branch = $defaultFinder();
        }

        return $branch;
    })
    ->setServiceName(getenv('TRAVIS') ? 'travis-ci' : null)
    ->setServiceJobId(getenv('TRAVIS_JOB_ID') ?: null)
    ->addDefaultWriter()
;

$runner->addReport($coverallsReport);
$script->php('php -dxdebug.overload_var_dump=0');

$script->testIt();