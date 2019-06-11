<?php
function isAuthenticated()
{
  return decodeAuthCookie();
}

function requireAuth()
{
  if (!isAuthenticated()) {
    global $session;
    $session->getFlashBag()->add('error', 'Not Authorized');
    redirect('/login.php');
  }
}

function isAdmin()
{
  if (!isAuthenticated()) {
    return false;
  }
  
  return decodeAuthCookie('auth_roles') === 1;
}

function requireAdmin()
{
  if (!isAdmin()) {
    global $session;
    $session->getFlashBag()->add('error', 'Not Authorized');
    redirect('/login.php');
  }
}

function isOwner($ownerId)
{
  if (!isAuthenticated()) {
    return false;
  }
  
  return $ownerId == decodeAuthCookie('auth_user_id');
}

function getAuthenticatedUser()
{
  return findUserById(decodeAuthCookie('auth_user_id'));
}

function saveUserData($user)
{
  global $session;
  
  $session->getFlashBag()->add('success', 'Successfully Logged In');
  $data = [
    'auth_user_id' => (int) $user['id'],
    'auth_roles' => (int) $user['role_id']
  ];
  $expTime = time() + 3600;
  $cookie = setAuthCookie(json_encode($data), $expTime);
  redirect('/', ['cookies' => [$cookie]]);
}
function setAuthCookie($data, $expTime)
{
  $cookie = new Symfony\Component\HttpFoundation\Cookie(
    'auth',
    $data,
    $expTime,
    '/',
    'localhost',
    false,
    true
  );
  return $cookie;
}
function decodeAuthCookie($prop = null)
{
  $cookie = json_decode(request()->cookies->get('auth'));
  if ($prop === null) {
    return $cookie;
  }
  if (!isset($cookie->$prop)) {
    return false;
  }
  return $cookie->$prop;
}