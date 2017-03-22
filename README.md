Nagios-WordPress-Update
===============

A Nagios plugin to check for WordPress version updates on a remote server without the use of NRPE.
This fork is written to accept multiple parameters instead of a single URL.  This is advantageous for Icinga2 users, who can add this to their host file to pick up the correct URL:

    vars.cms = "wordpress"
    # optional - for SSL
    vars.cms_use_ssl = true
The WordPress file "wp-version.php" is untouched from the original.

How to use:

- Upload wp-version.php to your WordPress root installation
- Include the IP address of your Nagios installation in the script
- Copy check\_wp\_update to your Nagios plugins folder. For me, it's on /usr/lib64/nagios/plugins
- Create a service command template
- Create a service check on your host

__Command Template (Icinga 2)__

    object CheckCommand "wordpress" {
      command = [
        PluginDir + "/check_wp_update",
        "-H", "$host_name$"
      ]

      arguments = {
        "-S" = {
          set_if = "$cms_use_ssl$"
        }
      }
    }


__Service Check (Icinga 2)__
    apply Service "wordpress" {
      import "generic-service"

      check_command = "wordpress"

      assign where host.vars.cms == "wordpress"
    }

Many thanks to jinjie for the original, found [here](https://github.com/jinjie/Nagios-WordPress-Update)
