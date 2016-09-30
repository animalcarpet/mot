// FIXED BRANCHES ----------------------------------------------------------------------------------------------------------
def ansible_mot2_branch = 'test/multibranchpipeline'
def buildscripts_branch = 'feature/timestamp_rpm'

// FIXED PARAMS ------------------------------------------------------------------------------------------------------------
def BEHAT_TAGS = '~@wip&&~@disabled&&~@quarantine&&~@slots&&~@test-quality-information'
def BEHAT_TAGS_TQI = 'test-quality-information'
def SELENIUM_HUB = 'http://selenium-1.fbmgmt.mot.aws.dvsa:4444/wd/hub/'

// PREP --------------------------------------------------------------------------------------------------------------------
def FB
def buildMOT
def buildHierdata
def buildPuppetcode
def trello_appKey = '9d8f607b3967fddcd062d71bf26a66a9'
def trello_token = '50fdf4021897216ed017732a5eb28eb65f9b21ec652c252bf01e23ec54a6a567'   // Ricky Walker :P
def trello_boardId = '57cedca102eca74fe5bca0b8'
def trello_availableListId = '57cedcbb1cfd28481ac4c081'
def trello_ciListId = '57cedcbe7ba83f3909d2b17c'

// DEFINITIONS -------------------------------------------------------------------------------------------------------------
def trelloCheckIfBranchHasFB(app_key,token,ci_list_id) {
  sh """
  set +x
  mkdir -p ${env.WORKSPACE}/temp
  export trello_ci_cards=`curl -s 'https://api.trello.com/1/lists/"""+ci_list_id+"""?fields=name&cards=open&card_fields=name&key="""+app_key+"""&token="""+token+"""'`
  echo \${trello_ci_cards} | jq -r '[ .cards[].id ][]' > ${env.WORKSPACE}/temp/ci_cards_ids
  while read id; do 
    export trello_ci_card=`curl -s "https://api.trello.com/1/cards/\${id}?key="""+app_key+"""&token="""+token+""""`
    echo \${trello_ci_card} | jq -r '[ .desc ][]' >> ${env.WORKSPACE}/temp/ci_cards_descs
    if [ `grep "${env.BRANCH_NAME}" ${env.WORKSPACE}/temp/ci_cards_descs | wc -l` -ne 0 ]; then 
      echo \${trello_ci_card} | jq -r .name > ${env.WORKSPACE}/temp/branch_assigned_fb
      echo -e "\n[INFO] Branch '${env.BRANCH_NAME}' already has '`cat ${env.WORKSPACE}/temp/branch_assigned_fb`' assigned. Continuing..."
      break
    fi
  done < ${env.WORKSPACE}/temp/ci_cards_ids
  """
}

def trelloGetAvailableCard(app_key,token,av_list_id) {
  sh """
  set +x
  export trello_available_fbs=`curl -s 'https://api.trello.com/1/lists/"""+av_list_id+"""?fields=name&cards=open&card_fields=name&key="""+app_key+"""&token="""+token+"""'`
  echo \${trello_available_fbs} | jq -r '[ .cards[0].name ][0]' > ${env.WORKSPACE}/temp/the_fb_card_name
  if [ `cat ${env.WORKSPACE}/temp/the_fb_card_name` = 'null' ]; then echo '[ERROR] No more available FB. Quitting...'; exit 1; fi
  echo \${trello_available_fbs} | jq -r '[ .cards[0].id ][0]' > ${env.WORKSPACE}/temp/the_fb_card_id
  """
}

def trelloMoveCardToList(app_key,token,card_id,list_id) {
  sh """
  set +x
  curl -s -X PUT 'https://api.trello.com/1/cards/"""+card_id+"""?idList="""+list_id+"""&key="""+app_key+"""&token="""+token+"""'
  if [ \$? -eq 0 ]; then echo -e '\n[OK] Card  successfully moved to CI list'
  else echo -e '\n[ERROR] Could not move card to CI list. Quitting...'; exit 1; fi
  """
}

def trelloAddCardDesc(app_key,token,card_id,desc) {
  sh """
  set +x
  curl -s -X PUT 'https://api.trello.com/1/cards/"""+card_id+"""?desc="""+desc+"""&key="""+app_key+"""&token="""+token+"""'
  """
}

def checkIfBranchExists(group, repo, creds) {
  sshagent([creds]) {
    sh """
    set +x
    mkdir -p ${env.WORKSPACE}/temp/${env.BRANCH_NAME}
    if [[ `git ls-remote --heads git@gitlab.motdev.org.uk:"""+group+"""/"""+repo+""".git ${env.BRANCH_NAME}` ]]; then
      echo 'REPO: """+repo+""" | BRANCH TO BE USED: ${env.BRANCH_NAME}'
      touch ${env.WORKSPACE}/temp/${env.BRANCH_NAME}/"""+repo+"""
    else
      echo 'REPO: """+repo+""" | BRANCH TO BE USED: master'
    fi
    """
  }
}

def createYumRepo(aws_access, aws_secret) {
  withEnv([   'ANSIBLE_HOST_KEY_CHECKING=no',
    'ANSIBLE_FORCE_COLOR=true',
    'ANSIBLE_HOSTS=ec2.py',
    'EC2_INI_PATH=mot2-fb-ec2.ini',
    'PYTHONUNBUFFERED=true',
    'AWS_DEFAULT_REGION=eu-west-1',
    'AWS_ACCESS_KEY_ID='+aws_access,
    'AWS_SECRET_ACCESS_KEY='+aws_secret
  ]) {
    dir('ansible-mot2') {
      sshagent(['ba60fadb-4090-4b7c-b809-c0c4a44c923f']) {
        sh """
        ansible-playbook mot2-deployment/src_ansible/playbook_yum_repository.yml -u deploy -i ec2.py -e "node=tag_nodetype_management repo_name=`echo ${env.BRANCH_NAME}`"
        """
      }
    }
  }
}

def check_out_repo(group, repo, gitbranch, creds){
  checkout poll: false, scm: [$class: 'GitSCM', branches: [[name: gitbranch]], doGenerateSubmoduleConfigurations: false, extensions: [[$class: 'RelativeTargetDirectory', relativeTargetDir: repo]], submoduleCfg: [], userRemoteConfigs: [[credentialsId: creds, url: 'git@gitlab.motdev.org.uk:' + group + '/' + repo +'.git']]]
}

def check_out_repo_no_branch(group, repo, creds){
  def branch_exists = fileExists("${env.WORKSPACE}/temp/${env.BRANCH_NAME}/"+repo)
  if(branch_exists) { env.repo_branch = "${env.BRANCH_NAME}" } else { env.repo_branch = 'master'}
  checkout poll: false, scm: [$class: 'GitSCM', branches: [[name: "${env.repo_branch}"]], doGenerateSubmoduleConfigurations: false, extensions: [[$class: 'RelativeTargetDirectory', relativeTargetDir: repo]], submoduleCfg: [], userRemoteConfigs: [[credentialsId: creds, url: 'git@gitlab.motdev.org.uk:' + group + '/' + repo +'.git']]]
}

def get_composer(){
  sh 'curl -sS https://getcomposer.org/installer | php'
}

def composer_install(path){
  sshagent(['313a82d3-f2e7-4787-837e-7517f3ce84eb']) {
    sh 'php composer.phar install -d ' + path
  }
}

def copy_configs(path){
  sh 'for x in ' + path + '/config/autoload/*.dist; do mv "$x" "${x%.*}" ; done'
}

def run_phpunit_independent(path) {
  sh 'cd ' + path + ' && ./vendor/bin/phpunit --group independent'
}

def push_rpm_2_yum_repo(repo,file_path) {
    sshagent(['ba60fadb-4090-4b7c-b809-c0c4a44c923f']) {
        sh 'rsync -avzR --rsync-path="sudo rsync" --no-owner --no-group -e "ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null" --progress '+file_path+' deploy@management-1:/srv/yum/'+repo+'/'
    }
}

def clean_repo(repo) {
    sshagent(['ba60fadb-4090-4b7c-b809-c0c4a44c923f']) {
        // sudo rm -f `repomanage -k 5 -o /srv/yum/'+repo+'`;
        sh 'ssh deploy@management-1 "sudo createrepo /srv/yum/'+repo+'"'
    }
}

def deploy(aws_access, aws_secret, fb, playbook, node, git_repo) {
  withEnv([   'ANSIBLE_HOST_KEY_CHECKING=no',
    'ANSIBLE_FORCE_COLOR=true',
    'ANSIBLE_HOSTS=ec2.py',
    'EC2_INI_PATH=mot2-fb-ec2.ini',
    'PYTHONUNBUFFERED=true',
    'AWS_DEFAULT_REGION=eu-west-1',
    'AWS_ACCESS_KEY_ID='+aws_access,
    'AWS_SECRET_ACCESS_KEY='+aws_secret
  ]) {
    dir('ansible-mot2') {
      sshagent(['ba60fadb-4090-4b7c-b809-c0c4a44c923f']) {
        sh """
        if [ -f ${env.WORKSPACE}/temp/${env.BRANCH_NAME}/"""+git_repo+""" ]; then export """+node+"""_yum_repo=${env.BRANCH_NAME}
        else export """+node+"""_yum_repo='master'; fi
        if [ -f ${env.WORKSPACE}/temp/${env.BRANCH_NAME}/puppet-code ]; then export puppet_yum_repo=${env.BRANCH_NAME}
        else export puppet_yum_repo='master'; fi
        if [ -f ${env.WORKSPACE}/temp/${env.BRANCH_NAME}/hieradata ]; then export hiera_yum_repo=${env.BRANCH_NAME}
        else export hiera_yum_repo='master'; fi
        ansible-playbook mot2-deployment/src_ansible/"""+playbook+""" -u deploy -i ec2.py -e "node=tag_fbnode_"""+fb+"""_"""+node+""" app_yum_repo=`echo \${"""+node+"""_yum_repo}` puppet_yum_repo=`echo \${puppet_yum_repo}` hiera_yum_repo=`echo \${hiera_yum_repo}`"
        """
      }
    }
  }
}

def run_phpunit(path) {
  sh 'cd ' + path + ' && ./vendor/bin/phpunit -c test/phpunit_ci.xml '
}

def run_behat(tags) {
  sh '''
  cd ..
  WORKSPACE=`pwd`
  cd $WORKSPACE/mot/mot-behat/config/
  consul-template -consul localhost:8500 -template "api.yml.ctmpl:api.yml" -once
  export APPLICATION_CONFIG_PATH=$WORKSPACE/app/config
  echo $APPLICATION_CONFIG_PATH
  export TEST_APPLICATION_CONFIG_PATH=$WORKSPACE/app/config
  echo $TEST_APPLICATION_CONFIG_PATH
  cd $WORKSPACE/mot/mot-behat
  bin/behat --format=junit --out=build --format=pretty --colors --out=std --tags="'''+tags+'''"
  '''
}

def prepare_selenium(hub) {
    sh """
    mkdir -p selenium-screenshots/error
    cp ~/selenium.properties ${env.WORKSPACE}/selenium.properties
    cp mot-selenium/src/main/resources/selenium/driver/grid/firefoxNoVersionLinux.properties ${env.WORKSPACE}/firefoxNoVersionLinux.properties
    if [ """+hub+""" != 'no' ]; then sed -i "s|test.gridUrl=.*|test.gridUrl="""+hub+"""|g" ${env.WORKSPACE}/selenium.properties; else sed -i 's/selenium/no/g' ${env.WORKSPACE}/firefoxNoVersionLinux.properties; if [ `ps aux | grep xinit | grep -v grep | wc -l` -eq 0 ]; then sudo nohup xinit < /dev/null > /dev/null 2>&1 & fi fi
    """
}

def run_selenium(hub,suite) {
    sh """
    if [ """+hub+""" == 'no' ]; then export DISPLAY=:0.0; fi
    export SELENIUM_DRIVER_PROPERTIES=${env.WORKSPACE}/firefoxNoVersionLinux.properties
    export SELENIUM_ENV_PROPERTIES=${env.WORKSPACE}/selenium.properties
    /usr/bin/mvn -B -f mot-selenium/pom.xml test -Dtest.screenshots.error.folder=selenium-screenshots/error -X -DtestngFile="""+ suite
}

def tag_repo(repo){
  dir(repo) {
    sshagent(['032a3717-f47d-4b4e-98bc-789aae33c968']) {
      sh 'git tag -a ci2_build_$BUILD_NUMBER -m "CI2 build number: $BUILD_NUMBER" && git push origin --tags'
    }
  }
}

// FIRE! -------------------------------------------------------------------------------------------------------------------
node('builder') {
  wrap([$class: 'TimestamperBuildWrapper']) {
  wrap([$class: 'AnsiColorBuildWrapper', colorMapName: 'xterm']) {
    deleteDir()
    env.WORKSPACE = pwd()  

    stage 'Get FB env'
    trelloCheckIfBranchHasFB(trello_appKey,trello_token,trello_ciListId)
    def FBassigned = fileExists("${env.WORKSPACE}/temp/branch_assigned_fb")
    if (FBassigned) { env.the_fb = readFile("${env.WORKSPACE}/temp/branch_assigned_fb").trim() }
    else {
      trelloGetAvailableCard(trello_appKey,trello_token,trello_availableListId)
      env.the_fb = readFile("${env.WORKSPACE}/temp/the_fb_card_name").trim()
      echo "First availale FB environment: ${env.the_fb}"
      env.the_fb_card_id = readFile("${env.WORKSPACE}/temp/the_fb_card_id").trim()
      trelloMoveCardToList(trello_appKey,trello_token,"${env.the_fb_card_id}",trello_ciListId)
      trelloAddCardDesc(trello_appKey,trello_token,"${env.the_fb_card_id}","BRANCH=>${env.BRANCH_NAME}")
    }
    FB = "${env.the_fb}"

    stage 'Create YUM repo'
    if (FBassigned) { echo "[INFO] Yum repo for branch '${env.BRANCH_NAME}' should already exist. Skipping..." }
    else {
      check_out_repo('webops', 'ansible-mot2', ansible_mot2_branch, '313a82d3-f2e7-4787-837e-7517f3ce84eb')
      withCredentials([[$class: 'UsernamePasswordMultiBinding', credentialsId: 'PRD_ANSIBLE_AWS_CREDENTIALS', usernameVariable: 'aws_key_id', passwordVariable: 'aws_secret_key']]) {
        createYumRepo("${env.aws_key_id}", "${env.aws_secret_key}")
      }
    }
  }} // wrappers
} // node

node(FB) {
  currentBuild.description = "FB: "+FB
  wrap([$class: 'TimestamperBuildWrapper']) {
  wrap([$class: 'AnsiColorBuildWrapper', colorMapName: 'xterm']) {
    deleteDir()
    env.WORKSPACE = pwd()

    stage 'Verify branch'
    parallel (
      'mot': { checkIfBranchExists('mot','mot','313a82d3-f2e7-4787-837e-7517f3ce84eb') },
      'hieradata': { checkIfBranchExists('webops','hieradata','313a82d3-f2e7-4787-837e-7517f3ce84eb') },
      'puppet-code': { checkIfBranchExists('webops','puppet-code','313a82d3-f2e7-4787-837e-7517f3ce84eb') },
      'manual': { checkIfBranchExists('mot','manual','313a82d3-f2e7-4787-837e-7517f3ce84eb') },
      'vehicle-service': { checkIfBranchExists('mot','vehicle-service','313a82d3-f2e7-4787-837e-7517f3ce84eb') },
      'authorisation-service': { checkIfBranchExists('mot','authorisation-service','313a82d3-f2e7-4787-837e-7517f3ce84eb') },
      'mot-test-service': { checkIfBranchExists('mot','mot-test-service','313a82d3-f2e7-4787-837e-7517f3ce84eb') },
      'jasper': { checkIfBranchExists('mot','jasperreports','313a82d3-f2e7-4787-837e-7517f3ce84eb') }
      // assets
      // any other repo that needs to be verified
    )

    stage 'Check out GIT repos'
    parallel (
      'buildscripts': { check_out_repo('mot','buildscripts',buildscripts_branch,'313a82d3-f2e7-4787-837e-7517f3ce84eb') },
      'mot': { check_out_repo_no_branch('mot','mot','313a82d3-f2e7-4787-837e-7517f3ce84eb') },
      'hieradata': { check_out_repo_no_branch('webops','hieradata','313a82d3-f2e7-4787-837e-7517f3ce84eb') },
      'puppet-code': { check_out_repo_no_branch('webops','puppet-code','313a82d3-f2e7-4787-837e-7517f3ce84eb') }   
    )
    dir('mot') {
      stage 'Independent unit tests'
      //copy_configs('mot-web-frontend')
      //run_phpunit_independent('mot-web-frontend')
    }

    stage 'Build MOT'
    buildMOT = fileExists("${env.WORKSPACE}/temp/${env.BRANCH_NAME}/mot")
    if (buildMOT) {
      dir('mot') {
        get_composer()
        composer_install('mot-api')
        composer_install('mot-common-web-module')
        composer_install('mot-testsupport')
        composer_install('mot-web-frontend')
        composer_install('mot-behat')
      }
      sh "env WORKSPACE=`pwd` bash -x buildscripts/code/rpm_mot-api-jenkins2.sh "+ FB
      sh 'mv mot/rpm mot/rpm-api'
      sh "env WORKSPACE=`pwd` bash -x buildscripts/code/rpm_mot-common-web-module-jenkins2.sh "+ FB
      sh 'mv mot/rpm mot/rpm-cwm'
      sh 'env WORKSPACE=`pwd` bash -x buildscripts/code/rpm_mot-web-frontend-jenkins2.sh'
      sh 'mv mot/rpm mot/rpm-frontend'
      sh "env WORKSPACE=`pwd` bash -x buildscripts/code/rpm_mot-testsupport-jenkins2.sh "+ FB
      sh 'mv mot/rpm mot/rpm-testsupport'
    }

    stage 'Build HIERA'
    withEnv(['PATH=$PATH:/opt/ruby22/bin/']) {
      buildHierdata = fileExists("${env.WORKSPACE}/temp/${env.BRANCH_NAME}/hieradata")
      if (buildHierdata) {
        dir('hieradata') {
          sh '''
          echo \"  :datadir:\" >> code/hiera.yaml
          sudo /opt/ruby22/bin/gem install -f --no-ri --no-rdoc bundler rake puppet-lint puppet hiera
          bash -x build-jenkins2 '''+ FB
        }
      } else { echo '[INFO] Skipping hieradata RPM build.' }
    }

    stage 'Build PUPPET'
    withEnv(['PATH=$PATH:/opt/ruby22/bin/']) {
      buildPuppetcode = fileExists("${env.WORKSPACE}/temp/${env.BRANCH_NAME}/puppet-code")
      if (buildPuppetcode) {
        dir('puppet-code') {
          sh '''
          sudo /opt/ruby22/bin/gem install -f --no-ri --no-rdoc bundler rake puppet-lint puppet hiera
          bash -x build-jenkins2 '''+FB
        }
      } else { echo '[INFO] Skipping puppet-code RPM build.' }
    }

    stage 'Upload RPMs'
    sh 'mkdir -p RPMS'
    if (buildMOT) { sh 'mv mot/*.rpm RPMS/' }
    if (buildHierdata) { sh 'mv hieradata/*.rpm RPMS/' }
    if (buildPuppetcode) { sh 'mv puppet-code/*.rpm RPMS/' }
    dir('RPMS'){
      push_rpm_2_yum_repo("${env.BRANCH_NAME}",'*.rpm')
    }
    clean_repo("${env.BRANCH_NAME}")
    
    stage 'Reset DB'
    dir('mot/mot-api/db') {
      sh './reset_db_with_test_data.sh '+FB+'_admin password mysql motdbuser'
    }

    stage 'Deploy'
    check_out_repo('webops', 'ansible-mot2', ansible_mot2_branch, '313a82d3-f2e7-4787-837e-7517f3ce84eb')
    withCredentials([[$class: 'UsernamePasswordMultiBinding', credentialsId: 'FB_AWS_CREDENTIALS', usernameVariable: 'aws_key_id', passwordVariable: 'aws_secret_key']]) {
      parallel (
        'consul': { deploy("${env.aws_key_id}", "${env.aws_secret_key}", FB, 'playbook_pipeline.yml', 'consul', '') },
        'openam': { deploy("${env.aws_key_id}", "${env.aws_secret_key}", FB, 'playbook_pipeline.yml', 'openam', '') },
        'opendj': { deploy("${env.aws_key_id}", "${env.aws_secret_key}", FB, 'playbook_pipeline.yml', 'opendj', '') }
      )
      parallel (
        'publishing': { deploy("${env.aws_key_id}", "${env.aws_secret_key}", FB, 'playbook_pipeline.yml', 'publishing', 'manual') },
        'frontend': { deploy("${env.aws_key_id}", "${env.aws_secret_key}", FB, 'playbook_pipeline.yml', 'frontend', 'mot') },
        'api': { deploy("${env.aws_key_id}", "${env.aws_secret_key}", FB, 'playbook_pipeline.yml', 'api', 'mot') },
        'vehicle': { deploy("${env.aws_key_id}", "${env.aws_secret_key}", FB, 'playbook_pipeline.yml', 'vehicle', 'vehicle-service') },
        'authr': { deploy("${env.aws_key_id}", "${env.aws_secret_key}", FB, 'playbook_pipeline.yml', 'authr', 'authorisation-service') },
        'mot_test': { deploy("${env.aws_key_id}", "${env.aws_secret_key}", FB, 'playbook_pipeline.yml', 'mot_test', 'mot-test-service') },
        'jasper': { deploy("${env.aws_key_id}", "${env.aws_secret_key}", FB, 'playbook_pipeline.yml', 'jasper', 'jasperreports') },
        'testsupport': { deploy("${env.aws_key_id}", "${env.aws_secret_key}", FB, 'playbook_pipeline.yml', 'testsupport', 'mot') }
      )
    }
    
    dir('mot') {
      stage 'Unit tests'
      copy_configs('mot-api')
      copy_configs('mot-testsupport')
      run_phpunit('mot-api')
      run_phpunit('mot-common-web-module')
      run_phpunit('mot-web-frontend')

      stage 'Behat tests'
      //sh '''
      //sed -i "s/'localhost',/'mysql',/g" mot-testsupport/config/autoload/global.php
      //sed -i "s/'password',/''''+FB+'''insecure1',/g" mot-testsupport/config/autoload/global.php
      //'''
      parallel (
        'behat': { run_behat(BEHAT_TAGS) },
        'behat_tqi': { run_behat(BEHAT_TAGS_TQI) }
      )

      stage 'Selenium BVT tests'
      prepare_selenium(SELENIUM_HUB)
      run_selenium(SELENIUM_HUB,'bvt.xml')

      stage 'Selenium Regression tests'
      run_selenium(SELENIUM_HUB,'regression.xml')
    }

    if ( "${env.CI_BUILD_BRANCH}" == "${env.BRANCH_NAME}") {
      stage 'Promote to CI'
      // dir('RPMS') {
      //   push_rpm_2_yum_repo('ci','*.rpm')
      // }
      // clean_repo('ci')
      // tag_repo('mot')
      // }
    }
  }} // wrappers
} // node
