<?php
function isAuthenticated()
{
    global $session;
    return $session->get('auth_logged_in',false);
}

function saveUserData($user)
{
    global $session;
    $session->set('auth_logged_in',true);
    $session->set('auth_user_id',(int) $user['id']);
    $session->set('auth_roles',(int) $user['role_id']);

    $session->getFlashBag()->add('success', 'Successfully Logged In');
    $data = [
        'auth_user_id' => (int) $user['id'],
        'auth_roles' => (int) $user['role_id']
    ];
    $expTime = time() + 3600;
    $cookie = setAuthCookie(json_encode($data), $expTime);
    redirect('/',['cookies' => [$cookie]]);
}

function requireAuth() {
    if (!isAuthenticated()){
        global $session;
        $session->getFlashBag()->add('error','Not Authorized');
        redirect('/login.php');
    }
}

function getAuthenticatedUser()
{
    global $session;
    return findUserById($session->get('auth_user_id'));
}

function isAdmin()
{
    if (!isAuthenticated()){
        return false;
    }

    global $session;
    return $session->get('auth_roles') === 1;
}

function requireAdmin()
{
    if(!isAdmin()){
        global $session;
        $session->getFlashBag()->add('error','Not Authorized');
        redirect('/login.php');
    }
}

function isOwner($ownerId)
{
    if (!isAuthenticated()){
        return false;
    }

    global $session;
    return $ownerId == $session->get('auth_user_id');
}

function setAuthCookie($data, $expTime)
{
    $cookie = new Symfony\Component\HttpFoundation\Cookie(
        'auth',
        $data,
        $expTime,
        '/',
        '.localhost',
        false,
        true
    );

    return $cookie;
}
