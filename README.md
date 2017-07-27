# Plugin Jenkins


Dokuwiki plugin for Jenkins user. Currently, this plugin only display Job last state and some information.

In the future, it will allow more things (such as displaying a specific build or triggering a job via Dokuwiki).

# Requirements

This plugin does not require any prerequisites.

# Installation

Download Redissue plugin into your `${dokuwiki_root}/lib/plugins` folder and restart dokuwiki. Or use the plugin manager of Dokuwiki.

# Configuration

You should configure Dokwuki-Jenkins in the Config Manager of Dokuwiki:

* **jenkins.url**: Put your Jenkins url here, without a slash ending. i.e.: `http://my-jenkins.com`.
* **jenkins.user**: Set a Jenkins user with admin right if possible to display any job.
* **jenkins.token**: Set the corresponding Jenkins user token. You can find it in `http://JENKINS_URL/user/USER/configure`, then click on `Show API Token`" button.

# Syntax

To use Dokuwiki-Jenkins, use the below syntax:

```html
<jenkins job="JOB_NAME" />
```

If you job is in a folder, please use it as follow:

```html
<jenkins job="FOLDER/JOB_NAME" />
```

That's all !

# Preview

![Plugin Preview](images/jenkins_preview.png)

For further information, see also [Dokuwiki-Jenkins on dokuwiki.org](https://www.dokuwiki.org/plugin:jenkins)
