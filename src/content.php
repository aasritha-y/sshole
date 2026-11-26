<?php
// All page content. HTML in 'html', 'body' and 'items' (except checklists) is authored here and
// printed unescaped; never put user input into it. Checklist items are plain text and escaped.

return [
    'title'    => 'sshole explained',
    'subtitle' => 'Why some Alpine Linux servers may let anyone log in to an account that is supposed to be locked, explained from the ground up.',

    'sections' => [

        [
            'id' => 'summary',
            'heading' => 'In short',
            'type' => 'callout',
            'html' => '
<p>A security researcher has reported that on servers running <strong>Alpine Linux</strong> (and other systems built the same way), the SSH login service can be tricked into letting someone into an account that was deliberately locked, such as <code>root</code>. The trick is to type a password that is <strong>longer than 256 characters</strong>. Any such password works; it does not need to be the right one.</p>
<p>It happens because of how two pieces of software fit together: <strong>musl</strong>, the small system library Alpine is built on, and <strong>OpenSSH</strong>, the SSH server. The safety net that most other Linux systems have in between, called <strong>PAM</strong>, is missing on Alpine by default, so nothing catches the mistake.</p>
<p>If you run Alpine servers that accept SSH password logins, turning password login off closes the hole straight away.</p>',
        ],

        [
            'id' => 'passwords',
            'heading' => 'First: how a Linux server checks your password',
            'type' => 'prose',
            'html' => '
<p>A server never stores your actual password. It stores a scrambled version of it, called a <strong>hash</strong>, in a protected file named <code>/etc/shadow</code>. A hash looks like <code>$6$abc$k2P9...</code>: the <code>$6$</code> says which scrambling method was used (SHA-512), <code>abc</code> is a random value called the <strong>salt</strong>, and the rest is the scrambled result.</p>
<p>When you log in, the server takes what you typed, scrambles it the same way with the same salt, and compares the two results. If they are identical, the password was right. The function that does the scrambling is called <code>crypt()</code>.</p>
<p>Some accounts should never be logged into with a password at all, for example <code>root</code> on a cloud server where everyone is supposed to use SSH keys. For those, the hash is replaced with a single <code>*</code>. Since <code>*</code> is not a valid scrambled result of anything, no password can ever match it. The account is effectively locked for passwords, while SSH keys can still be used. This convention goes back to Unix in 1980.</p>',
        ],

        [
            'id' => 'musl',
            'heading' => 'What musl is, and why Alpine uses it',
            'type' => 'prose',
            'html' => '
<p>Every Linux program relies on a <strong>C library</strong> (often called <em>libc</em>) for basic jobs like reading files, allocating memory and, here, scrambling passwords with <code>crypt()</code>. Most Linux distributions, such as Debian, Ubuntu and Fedora, use <strong>glibc</strong>, the large GNU C library.</p>
<p><strong>musl</strong> is a different C library, designed to be small, simple and easy to audit. Alpine Linux uses musl instead of glibc, which is a big part of why Alpine is so tiny (a base Alpine image is a few megabytes). That small size makes Alpine extremely popular as the base for Docker containers, and it is also common on routers and embedded devices through OpenWrt, Buildroot and Yocto.</p>
<p>The detail that matters here: when musl\'s <code>crypt()</code> cannot do its job, it does not return "nothing" like the official standard describes. Since 2012 it returns the single character <code>*</code> instead, so that programs which forget to check for errors do not crash. And musl refuses to scramble passwords longer than 256 bytes (a protection against someone slowing the server down with huge passwords), which counts as a failure. So a password over 256 characters makes musl\'s <code>crypt()</code> hand back <code>*</code>.</p>',
        ],

        [
            'id' => 'pam',
            'heading' => 'What PAM is',
            'type' => 'prose',
            'html' => '
<p><strong>PAM</strong> stands for <strong>Pluggable Authentication Modules</strong>. It is a shared layer that sits between programs that need to log people in (SSH, the console login screen, <code>sudo</code>) and the actual checking of who someone is.</p>
<p>Without PAM, every program would need its own code for reading <code>/etc/shadow</code>, checking passwords and deciding whether an account is locked. With PAM, the program simply asks "is this the right password for this user?" and PAM answers, using whichever modules the administrator has configured. Those modules can check the local password file, a company directory such as LDAP, a one-time code app, and so on. Think of PAM as a building\'s central security desk: each door does not need its own guard, they all phone the desk.</p>
<p>On most Linux distributions, OpenSSH is built to use PAM. PAM\'s standard password module recognises that a <code>*</code> in the password file means "locked" and refuses straight away, without even trying to scramble the password. That is the safety net.</p>',
        ],

        [
            'id' => 'alpine',
            'heading' => 'Why Alpine is the system at risk',
            'type' => 'prose',
            'html' => '
<p>Alpine combines both conditions needed for the problem:</p>
<ol class="plain">
  <li><strong>It uses musl</strong>, so a password over 256 characters makes <code>crypt()</code> return <code>*</code>.</li>
  <li><strong>Its standard OpenSSH server is built without PAM.</strong> Alpine keeps things minimal, and PAM support is not part of the default SSH server. So instead of asking PAM, OpenSSH checks the password itself: it calls <code>crypt()</code> and compares the result with what is stored in <code>/etc/shadow</code>.</li>
</ol>
<p>Put those together and a locked account stores <code>*</code>, musl returns <code>*</code> for the over-long password, OpenSSH compares <code>*</code> with <code>*</code>, sees a match, and lets the person in.</p>
<p>A Debian or Ubuntu server does not have this problem, for two separate reasons: it uses glibc, whose <code>crypt()</code> does not return a bare <code>*</code>, and its SSH server goes through PAM, which rejects locked accounts before <code>crypt()</code> is ever called.</p>',
        ],

        [
            'id' => 'chain',
            'heading' => 'The bug, step by step',
            'type' => 'chain',
            'items' => [
                'An account is locked for passwords, so <code>/etc/shadow</code> stores just <code>*</code> for it.',
                'An attacker connects over SSH and types a password longer than 256 characters.',
                'Because there is no PAM, OpenSSH asks musl\'s <code>crypt()</code> to scramble that password.',
                'musl refuses anything over 256 bytes, treats it as a failure, and returns <code>*</code>.',
                'OpenSSH compares the returned <code>*</code> with the stored <code>*</code>. They are equal, so it decides the password was correct and logs the attacker in.',
            ],
        ],

        [
            'id' => 'affected',
            'heading' => 'Are you affected?',
            'type' => 'checklist',
            'intro' => 'According to the write-up, a server is at risk only when <strong>all</strong> of these are true:',
            'bad' => [
                'The SSH server runs on musl (Alpine, OpenWrt, Buildroot, Yocto, many Docker images built on Alpine).',
                'OpenSSH is not using PAM (the Alpine default, or UsePAM set to no).',
                'Password logins are allowed (the default unless someone turned them off).',
                'At least one account has just * as its password, such as a key-only deploy user, an LDAP user, or root on Alpine.',
                'Some other account on the server has a real password set.',
            ],
            'outro' => 'The write-up highlights Alpine\'s official AWS and Azure images: their default <code>alpine</code> user is locked this way and can run admin commands without a password through <code>doas</code> (Alpine\'s equivalent of <code>sudo</code>), so getting into it amounts to full control of the server.',
            'good_heading' => 'Reported as not affected',
            'good' => [
                'Debian, Ubuntu, Fedora and other glibc-based systems.',
                'Any system where OpenSSH goes through PAM.',
                'Dropbear, a different SSH server often used on small devices.',
                'OpenBSD.',
            ],
        ],

        [
            'id' => 'fix',
            'heading' => 'What to do',
            'type' => 'list',
            'items' => [
                '<strong>Turn off password logins</strong> if you do not need them. Add a file <code>/etc/ssh/sshd_config.d/00-no-passwords.conf</code> containing <code>PasswordAuthentication no</code>, then restart SSH with <code>rc-service sshd restart</code>. This removes the hole regardless of version. Make sure your SSH key works first so you do not lock yourself out.',
                '<strong>Update OpenSSH.</strong> The write-up says version 10.6p2 fixes it (<code>apk upgrade openssh</code> on Alpine). Confirm this against Alpine\'s and OpenSSH\'s own security advisories.',
                '<strong>Check whether it was used against you.</strong> Search your SSH logs for <code>Accepted password for</code> on accounts that should only ever use keys, especially <code>root</code> and <code>alpine</code>.',
                '<strong>Rebuild Docker images</strong> based on Alpine after the fix is available, since containers keep the version they were built with.',
            ],
        ],

        [
            'id' => 'confidence',
            'heading' => 'What is confirmed and what is not',
            'type' => 'prose',
            'html' => '
<p>This page explains the issue as described in the public write-up at <a href="https://sshhole.com/" rel="noopener noreferrer">sshhole.com</a>. The background facts (how password hashes work, what musl and PAM are, that Alpine uses musl) are well established. The vulnerability itself, the version that fixes it, and the researcher\'s testing (about 17,600 login attempts against differently built servers) have <strong>not been independently verified</strong> here. Check the linked sources and the official musl, OpenSSH and Alpine announcements before acting on the details.</p>',
        ],
    ],

    'timeline_heading' => 'How it came about',
    'timeline_intro' => 'No single change caused this. Each step below was reasonable on its own; the problem only appears when they meet.',
    'timeline' => [
        ['year' => '1980', 'heading' => '* means "locked"',
         'body' => 'Early Unix marks accounts nobody should log into with a <code>*</code> instead of a password hash.',
         'note' => '3BSD /etc/passwd', 'link' => 'https://github.com/dspinellis/unix-history-repo/blob/BSD-3/etc/passwd'],
        ['year' => '1997', 'heading' => 'crypt() should return nothing on failure',
         'body' => 'The Unix standard says <code>crypt()</code> returns NULL (an empty "no result") when it fails. Many programs never check for it and crash.',
         'note' => 'Single UNIX Specification, crypt()', 'link' => 'https://pubs.opengroup.org/onlinepubs/7908799/xsh/crypt.html'],
        ['year' => 'Aug 2012', 'heading' => 'musl returns * instead',
         'body' => 'To stop those crashes, musl makes a failed <code>crypt()</code> return <code>*</code>, which can never match a real hash. The same character already means "locked".',
         'note' => 'musl commit b3c4cc12', 'link' => 'https://git.musl-libc.org/cgit/musl/commit/?id=b3c4cc12'],
        ['year' => 'Aug 2012', 'heading' => 'musl caps passwords at 256 bytes',
         'body' => 'SHA-512 scrambling gets slower with longer input, so musl refuses anything over 256 bytes. Refusing counts as a failure, so it returns <code>*</code>.',
         'note' => 'musl commit 88bf5a8a', 'link' => 'https://git.musl-libc.org/cgit/musl/commit/?id=88bf5a8a'],
        ['year' => '2016', 'heading' => 'OpenSSH starts hashing for every account',
         'body' => 'CVE-2016-6210: attackers could tell which usernames exist by timing failed logins. The fix makes OpenSSH always run <code>crypt()</code>, even for accounts it would otherwise reject quickly. The write-up dates the exposure from here.',
         'note' => 'openssh-commits, July 2016', 'link' => 'https://lists.mindrot.org/pipermail/openssh-commits/2016-July/005428.html'],
        ['year' => '2019 to 2022', 'heading' => 'Alpine locks root with *',
         'body' => 'After CVE-2019-5021 (Alpine Docker images shipped with an empty root password), Alpine first locked root with <code>!</code>, which also blocked SSH keys without PAM. Alpine 3.16 switched to <code>*</code>, which keeps keys working and, per the write-up, made root reachable through this bug.',
         'note' => 'alpine aports commit fbe926af', 'link' => 'https://gitlab.alpinelinux.org/alpine/aports/-/commit/fbe926af.patch'],
        ['year' => 'Nov 2026', 'heading' => 'Reported',
         'body' => 'The researcher built OpenSSH in every combination (glibc or musl, with or without PAM) and found only musl without PAM accepted logins it should have refused.',
         'note' => 'sshhole.com', 'link' => 'https://sshhole.com/'],
    ],

    'glossary' => [
        'SSH' => 'Secure Shell, the standard way to log into and control a server remotely. OpenSSH is the most widely used SSH server.',
        'Hash' => 'A one-way scrambled version of a password. The server stores this, never the password itself.',
        'Salt' => 'A random value mixed into each password before hashing, so two people with the same password get different hashes.',
        'crypt()' => 'The system function that turns a password and salt into a hash.',
        'libc' => 'The core C library every Linux program depends on. glibc and musl are two different ones.',
        'musl' => 'A small, simple C library used by Alpine Linux instead of glibc.',
        'PAM' => 'Pluggable Authentication Modules, the shared layer most Linux systems use to check logins on behalf of programs like SSH.',
        '/etc/shadow' => 'The protected file that stores each account\'s password hash, or * for accounts locked to passwords.',
    ],

    'footer' => 'ssh hole',
];
