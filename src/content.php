<?php
// All page content lives here so index.php stays a plain template.
// Fields marked "trusted HTML" are authored here, never user input.

return [
    'title'    => 'sshole: explained',
    'subtitle' => 'did you get hacked? check.',

    'intro' => 'OpenSSH on musl Linux (Alpine, OpenWrt, Buildroot, Yocto, and many Docker images) '
        . 'is described as having a problem since 2016. Accounts that should never take a password '
        . '(root, key-only deploy users, LDAP users, the default user on Alpine cloud images) are '
        . 'said to accept any password longer than <strong>256 characters</strong>.',

    // trusted HTML in heading and body
    'timeline' => [
        [
            'year' => '1980',
            'heading' => 'the first star',
            'body' => 'In old Unix the password hash sat in <code>/etc/passwd</code>, next to the username, in a world-readable file. A single <code>*</code> was put there for the <code>daemon</code> account, which nobody should log in as. A <code>*</code> is not a hash, so no password can match it. Unix still does this today.',
            'note' => '3BSD, /etc/passwd, 1980',
            'link' => 'https://github.com/dspinellis/unix-history-repo/blob/BSD-3/etc/passwd',
        ],
        [
            'year' => '1990s',
            'heading' => 'how sshd checks your password',
            'body' => 'Today the hash lives in <code>/etc/shadow</code> and looks like <code>$6$abc$k2P9...</code>. The <code>$6$</code> means SHA-512, <code>abc</code> is the salt, and the rest is the hash. You type a password, <code>sshd</code> hashes it with the same salt, and checks whether the result is identical. If the field is just <code>*</code>, there is nothing to match, so no password gets you in.',
            'note' => null,
            'link' => null,
        ],
        [
            'year' => '1990s',
            'heading' => 'the fine print',
            'body' => 'If <code>crypt()</code> cannot do its job, it returns <code>NULL</code>. For example, the salt may ask for an algorithm the system does not have. Many programs never check for <code>NULL</code>. They compare whatever <code>crypt()</code> returns with the stored field and crash.',
            'note' => 'The Single UNIX Specification, crypt(), 1997',
            'link' => 'https://pubs.opengroup.org/onlinepubs/7908799/xsh/crypt.html',
        ],
        [
            'year' => '2012',
            'heading' => 'crypt stops saying NULL',
            'body' => 'musl (the C library under Alpine) decides <code>crypt()</code> will no longer return <code>NULL</code>. On failure it returns a single character that can never match a real hash, so programs see a wrong password instead of crashing. The character chosen was <strong>*</strong>, the same character used to mark locked accounts.',
            'note' => 'musl, b3c4cc12, aug 2012',
            'link' => 'https://git.musl-libc.org/cgit/musl/commit/?id=b3c4cc12',
        ],
        [
            'year' => '2012',
            'heading' => 'three weeks later',
            'body' => 'musl adds SHA-512 crypt. It gets slower as the password gets longer, so a huge password could keep a server busy. musl caps input at <strong>256 bytes</strong> and refuses anything longer. Past 256 bytes <code>crypt()</code> fails, and when it fails it returns <code>*</code>.',
            'note' => 'musl, 88bf5a8a, aug 29 2012',
            'link' => 'https://git.musl-libc.org/cgit/musl/commit/?id=88bf5a8a',
        ],
        [
            'year' => '2016',
            'heading' => 'sshd borrows a salt',
            'body' => 'CVE-2016-6210. Asking <code>sshd</code> about a user that does not exist returned instantly, because there was nothing to hash. Real users took a few milliseconds longer, so timing revealed who exists. The fix: when there is no real hash, <code>sshd</code> hashes your password against another account\'s salt anyway, to spend the same time.',
            'note' => 'openssh-commits, jul 2016',
            'link' => 'https://lists.mindrot.org/pipermail/openssh-commits/2016-July/005428.html',
        ],
        [
            'year' => '2019 to 2022',
            'heading' => 'the fix for the fix',
            'body' => '2019: Alpine Docker images had no root password at all (CVE-2019-5021). Alpine locked root with <code>!</code>, but without PAM that also blocks SSH keys, and cloud images log in with keys. 2022: Alpine 3.16 switched root to <code>*</code>. Still locked, and keys work again.',
            'note' => 'alpine aports, fbe926af, shipped in 3.16, 2022',
            'link' => 'https://gitlab.alpinelinux.org/alpine/aports/-/commit/fbe926af.patch',
        ],
        [
            'year' => '2016 to 2026',
            'heading' => 'ten quiet years',
            'body' => 'Why did nobody notice? A fresh box with no passwords set is not affected. A <code>*</code> in shadow needs a wrong try first. OpenSSH is tested on OpenBSD and glibc, musl does not test sshd, and most distros go through PAM, which never calls <code>crypt()</code> for a <code>*</code>. As far as anyone knows, nobody noticed.',
            'note' => 'libc-alpha, debian #714219, 2013',
            'link' => 'https://sourceware.org/legacy-ml/libc-alpha/2013-07/msg00046.html',
        ],
        [
            'year' => 'nov 2026',
            'heading' => 'how it was found',
            'body' => 'The author built <code>sshd</code> many ways (glibc, musl, PAM, no PAM, shadow, no shadow) and sent about 17,600 unusual logins at each. Every build agreed on every login except musl without PAM.',
            'note' => 'summary of the public write-up; not independently verified',
            'link' => null,
        ],
    ],

    // trusted HTML
    'chain' => [
        'Locked account stores <code>*</code> in <code>/etc/shadow</code>.',
        'You send a password longer than 256 bytes.',
        'musl\'s <code>crypt()</code> fails and returns <code>*</code>.',
        'sshd compares that <code>*</code> to the stored <code>*</code>.',
        'They match, so login is granted to a locked account.',
    ],

    // plain text, escaped on output
    'affected' => [
        'sshd running on musl (Alpine, OpenWrt, Buildroot, Yocto)',
        'not using PAM (Alpine\'s openssh-server does not by default, or UsePAM no)',
        'password logins still on (the default)',
        'an account with just * as its password (key-only users, LDAP users, root on Alpine)',
        'some other account with a real password',
    ],
    'not_affected' => [
        'Debian, Ubuntu, Fedora, anything with glibc',
        'any distro going through PAM',
        'Dropbear',
        'OpenBSD',
    ],
    'affected_note' => 'If all of the above are true, a <code>*</code> account is said to accept any password between 257 and 1024 characters. On Alpine\'s official AWS and Azure images that is the default <code>alpine</code> user, which has passwordless <code>doas</code>, so it is effectively root.',

    // trusted HTML
    'remediation' => [
        'Upgrade OpenSSH to 10.6p2 or later (version as stated by the write-up; confirm against the OpenSSH and Alpine advisories).',
        'Restart sshd after upgrading (<code>rc-service sshd restart</code> on Alpine).',
        'If you cannot upgrade: set <code>PasswordAuthentication no</code> and restart sshd.',
        'Check logs for <code>Accepted password for</code> on accounts that should be locked.',
    ],

    'footer' => 'Reference summary of the public write-up at sshhole.com. The claims have not been independently verified: confirm against the musl, OpenSSH and Alpine sources before acting. Test only on systems you own or have written authorization to test.',
];
