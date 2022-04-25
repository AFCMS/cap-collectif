import os
from fabric.api import env
from fabric.colors import cyan
from fabric.operations import local, settings
from task import task

capcobot = {
    'user': 'capco',
    'email': 'capco.bot@gmail.com',
    'pass': 'elephpant-can-fly',
}


# Usage:
#
# Runn tests: fab local.qa.phpspec
#
# Create a new spec from existing class :
# fab local.qa.phpspec:desc=Capco/AppBundle/GraphQL/Resolver/Questionnaire/QuestionnaireExportResultsUrlResolver
#
@task(environments=['local'])
def phpspec(desc=False):
    "Run PHP Unit Tests"
    if desc:
        local(
            'php -d pcov.enabled=1 -d pcov.directory=. -d pcov.exclude="~vendor~" -d memory_limit=-1 bin/phpspec describe ' + desc,
            'application', env.www_app)
    else:
        local(
            'php -d pcov.enabled=1 -d pcov.directory=. -d pcov.exclude="~vendor~" -d memory_limit=-1 bin/phpspec run --no-code-generation --no-coverage',
            'application', env.www_app)


@task(environments=['ci'])
def perf():
    "Run perf Tests"
    # env.compose(
    #     'run -e CI=true -e CIRCLECI -e CIRCLE_PROJECT_USERNAME -e CIRCLE_PROJECT_REPONAME -e CIRCLE_SHA1 -e CIRCLE_BRANCH qarunner yarn run bundlesize')


@task(environments=['local', 'ci'])
def graphql_schemas(checkSame=False):
    "Generate GraphQL schemas"
    for schema in ['public', 'preview', 'internal']:
        env.service_command(
            'bin/console graphql:dump-schema --env dev --schema ' + schema + ' --no-debug --file schema.' + schema + '.graphql --format graphql',
            'application', env.www_app)
    if checkSame:
        local(
            'if [[ $(git diff -G. --name-only *.graphql | wc -c) -ne 0 ]]; then git --no-pager diff *.graphql && echo "\n\033[31mThe following schemas are not up to date:\033[0m" && git diff --name-only *.graphql && echo "\033[31mYou should run \'yarn generate-graphql-files\' to update your *.graphql files !\033[0m" && exit 1; fi',
            capture=False, shell='/bin/bash')


# Usage:
#
# Generate all snapshots (delete all previous snapshots) :
# fab local.qa.snapshots
#
# Generate only snapshots, you are working on (@dev tag) :
# fab local.qa.snapshots:tags=dev
#
@task(environments=['local'])
def snapshots(tags='false'):
    "Generate all snapshots"
    env.service_command('mysqldump --opt -h database -u root symfony > var/db.backup', 'application', env.www_app)
    export_commands = [
        'capco:export:users --quiet --updateSnapshot --delimiter ","',
        'capco:export:questionnaire --quiet --updateSnapshot --delimiter ","',
        'capco:export:consultation --quiet --updateSnapshot --delimiter ","',
        'capco:export:projects-contributors --quiet --updateSnapshot --delimiter ","',
        'capco:export:step-contributors --quiet --updateSnapshot --delimiter ","',
        'capco:export:proposalStep --quiet --updateSnapshot --delimiter ","',
        'capco:export:events:participants --quiet --updateSnapshot --delimiter ","',
    ]
    user_archives_commands = [
        'capco:export:user userAdmin --updateSnapshot --delimiter ","',
        'capco:export:user user1 --updateSnapshot --delimiter ","',
        'capco:export:user user5 --updateSnapshot --delimiter ","',
    ]
    extensions = [
        'csv',
        'xlsx',
        'xls',
    ]

    print
    cyan('/!\ Your database must be up to date, to generate accurate snapshots !')

    if tags == 'false':
        print
        cyan('Deleting email snapshots...')
        local('rm -rf __snapshots__/emails/*')
    for suite in ['api', 'e2e', 'commands']:
        env.service_command('UPDATE_SNAPSHOTS=true php -d memory_limit=-1 ./bin/behat -p ' + suite + ' ' +
                            ('--tags=snapshot-email', '--tags=snapshot-email&&' + tags)[tags != 'false'], 'application',
                            env.www_app)
    print
    cyan('Successfully generated emails snapshots !')

    if tags == 'false':
        print
        cyan('Running user RGPD archive commands...')
        for command in user_archives_commands:
            env.service_command('bin/console ' + command + ' --env test --no-debug', 'application', env.www_app)
        print
        cyan('Successfully generated user RGPD archive snapshots !')

    env.service_command('bin/console capco:toggle:enable export --env test --no-debug', 'application', env.www_app)

    if tags == 'false':
        print
        cyan('Deleting exports snapshots...')
        for extension in extensions:
            env.service_command('rm -rf __snapshots__/exports/*.' + extension, 'application', env.www_app, 'root')

        print
        cyan('Running export commands...')
        for command in export_commands:
            env.service_command('bin/console ' + command + ' --env test --no-debug', 'application', env.www_app)

    print
    cyan('Successfully generated snapshots !')


@task(environments=['local', 'ci'])
def restore_db():
    env.service_command('mysql -h database -u root symfony < var/db.backup', 'application', env.www_app, "capco", False)


@task(environments=['local', 'ci'])
def save_db():
    env.service_command('mysqldump --opt -h database -u root symfony > var/db.backup', 'application', env.www_app, "capco", False)


@task(environments=['local', 'ci'])
def save_es_snapshot():
    f = open("var/data.json", "w")
    f.write("""
{
  "type": "fs",
  "settings": {
    "location": "var"
  }
}
    """)
    f.close()
    env.service_command("""
curl -XPUT "http://elasticsearch:9200/_snapshot/repository_qa" -H "Content-Type: application/json" -d @var/data.json
    """, 'application', env.www_app, "capco", False)
    env.service_command("""
curl -X DELETE "http://elasticsearch:9200/_snapshot/repository_qa/snap_qa?pretty"
    """, 'application', env.www_app, "capco", False)
    f = open("var/data.json", "w")
    f.write("""
{
   "indices": "capco"
}
    """)
    f.close()
    env.service_command("""
curl -XPUT "http://elasticsearch:9200/_snapshot/repository_qa/snap_qa?wait_for_completion=true" -H "Content-Type: application/json" -d @var/data.json
""", 'application', env.www_app, "capco", False)
    os.remove("var/data.json")


@task(environments=['local', 'ci'])
def restore_es_snapshot():
    env.service_command("""
curl -XPOST "http://elasticsearch:9200/capco/_close"
""", 'application', env.www_app, "capco", False)
    env.service_command("""
curl -XPOST "http://elasticsearch:9200/_snapshot/repository_qa/snap_qa/_restore?wait_for_completion=true"
""", 'application', env.www_app, "capco", False)
    env.service_command("""
curl -XPOST "http://elasticsearch:9200/capco/_open"
""", 'application', env.www_app, "capco", False)
    f = open("var/data.json", "w")
    f.write("""
{
  "actions":[{"remove":{"index":"*","alias":"capco_indexing"}},{"remove":{"index":"*","alias":"capco"}},{"add":{"index":"capco","alias":"capco_indexing"}},{"add":{"index":"capco","alias":"capco"}}]
}
""")
    f.close()
    env.service_command("""
curl -XPOST "http://elasticsearch:9200/_aliases" -H "Content-Type: application/json" -d @var/data.json
""", 'application', env.www_app, "capco", False)
    os.remove("var/data.json")


@task(environments=['local', 'ci'])
def behat(fast_failure='true', profile=False, suite='false', tags='false', timer='true'):
    "Run Gherkin Tests"
    env.service_command('mysqldump --opt -h database -u root symfony > var/db.backup', 'application', env.www_app)
    if profile:
        profiles = [profile]
    else:
        profiles = ['api', 'commands', 'e2e']

    php_option = ''
    env_option = '--format=junit --out=./coverage --format=pretty --out=std'

    if env.environment == 'ci':
        php_option = '-dpcov.enabled=1'

    for job in profiles:
        command = ('php ' + php_option + ' -d memory_limit=-1 ./bin/behat ' + env_option + ('', ' --log-step-times')[
            timer != 'false'] + ' -p ' + job + ('', '  --suite=' + suite)[suite != 'false'] + ('', '  --tags=' + tags)[
                       tags != 'false'] + ('', '  --stop-on-failure')[fast_failure == 'true'])
        env.service_command(command, 'application', env.www_app, 'root')


@task(environments=['local'])
def view(firefox=False):
    if env.dinghy:
        local('echo "secret" | open vnc://`docker-machine ip dinghy`')
    else:
        if firefox:
            local('echo "secret" | nohup vncviewer localhost:5901 &')
        else:
            local('echo "secret" | nohup vncviewer localhost:5900 &')


@task(environments=['local'])
def clear_fixtures():
    local(
        'docker ps -a | awk \'{ print $1,$2 }\' | grep capco/fixtures | awk \'{print $1 }\' | xargs -I {} docker rm -f {}')


@task(environments=['local'])
def kill_database_container():
    with settings(warn_only=True):
        local('docker ps -a | grep databasefixtures | awk \'{print $1}\' | xargs -I {} docker kill {}')


@task(environments=['local'])
def save_fixtures_image(tag='latest', publish='false'):
    "Publish a new fixtures image"
    env.service_command('php bin/console capco:reinit --force --no-toggles', 'application', env.www_app)
    env.service_command(
        'mysqldump -h database -uroot --opt symfony > infrastructure/services/databasefixtures/dump.sql', 'application',
        env.www_app, 'root')
    local('docker build -t capco/fixtures:latest infrastructure/services/databasefixtures')
    if publish != 'false':
        local('docker login -e ' + capcobot['email'] + ' -u ' + capcobot['user'] + ' -p ' + capcobot['pass'])
        local('docker push capco/fixtures')


@task(environments=['local'])
def blackfire_curl(url):
    "Blackfire curl"
    if env.dinghy:
        local('eval "$(docker-machine env dinghy)" && docker exec -i capco_application_1 blackfire curl '
        + url+ '--insecure --env="Capco.Dev"')
    else:
        local('eval docker exec -u root -i capco_application_1 blackfire curl ' + url+ '--insecure')


@task(environments=['local'])
def blackfire_run(cli):
    "Blackfire run"
    if env.dinghy:
        local('eval "$(docker-machine env dinghy)" && docker exec -u root -i capco_application_1 blackfire run ' + cli +
         ' --env="Cap Collectif / Capco.Dev"')
    else:
        local('eval docker exec -u root -i capco_application_1 blackfire run ' + cli)
