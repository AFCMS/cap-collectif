from __future__ import with_statement

import requests
import os

from fabric.api import *
from fabric.context_managers import lcd
from fabric.colors import red, green
from libsaas.services import github

GH_USER  = 'jolicode'
REPO     = 'CapCollectif-SF2'

env.circleci  = False
env.local_dir = env.real_fabfile[:-10]

@task
def circleci():
    env.circleci = True
    env.gh_token = os.environ['GH_TOKEN']

def create_PR_comment(type, result, pr):
    if os.getenv('GH_TOKEN', True):
        try:
            prId      = int(pr.split('/')[-1])
            gh_client = github.Github(env.gh_token)
            gh_pr     = gh_client.repo(GH_USER, REPO).issue(prId).comments().create('Warning: You should check your '+type+' files.\n '+result)
        except ValueError:
            print 'Can\'t retrieve pr id'

@task
def build():
    with lcd(env.local_dir):
        local('npm install')
        local('bower install --config.interactive=false')
        local('gulp build')
        local('composer install --prefer-source --no-interaction --optimize-autoloader')

@task
def build_test_db():
    with lcd(env.local_dir):
        local('php app/console doctrine:schema:update --force -e test')

@task
def reinit_data():
    with lcd(env.local_dir):
        local('php app/console capco:reinit --force')

@task
def lint(pr=''):
    with lcd(env.local_dir):
        with settings(warn_only=True):
            result = local('./php-cs-fixer fix . --config=sf23 --dry-run', capture=True)

            if result.return_code == 0:
                print(green('PHP: OK'))
            elif result.return_code == 1:
                print result
                print(red('PHP: /!\ You should fix your PHP files!'))

                if env.circleci:
                    create_PR_comment('PHP', result, pr)
            else: #print error to user
                print result
                raise SystemExit()

@task
def test():
    with lcd(env.local_dir):
        result = local('bin/phpspec run', capture=False)
        if result.return_code == 0:
            print(green('Tests: OK'))
        elif result.return_code == 1:
            print result
            print(red('Tests: /!\ KO!'))
    with lcd(env.local_dir):
        result = local('bin/behat', capture=False)
        if result.return_code == 0:
            print(green('Tests: OK'))
        elif result.return_code == 1:
            print result
            print(red('Tests: /!\ KO!'))
