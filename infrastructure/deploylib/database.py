from task import task
from fabric.api import env

environements = ['dev', 'test', 'prod']

@task(environments=['local', 'ci'])
def generate(migrate='false', environement='dev'):
    "Generate database"
    if environement in environements:
        # Delete media cache and source files
        env.service_command('rm -rf public/media/*', 'application')
        env.service_command('mkdir -p public/media', 'application')
        env.service_command('php -d memory_limit=1024M bin/console capco:reinit --force --env=' + environement + ('', ' --migrate')[migrate == 'true'], 'application', env.www_app)
    else:
        print "Option environment must be 'dev', 'test' or 'prod'."

@task(environments=['local'])
def mysqlconsole():
    "Access mysql"
    env.service_command('mysql -u root -h 127.0.0.1 symfony', 'application')


@task(environments=['local'])
def importbdd():
    "Import dump.sql"
    env.service_command('cat dump.sql | mysql -u root symfony', 'application')
