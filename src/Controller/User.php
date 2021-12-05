<?php

namespace App\Controller;

use App\View\View;
use App\Helper\Redirect;
use App\Model\User as UserModel;
use App\Model\Permission as PermissionModel;


class User
{
	public function index()
	{
		$view = new View();
		$model = new UserModel();

		return $view->template('userList')->with(['users' => $model->getAll(), 'hello' => 'Witaj',]);
	}

	public function add()
	{
		$view = new View();

		return $view->template('userAdd');
	}

	public function create()
	{
		$login = $_POST['login'];
		$password = \password_hash($_POST['password'], \PASSWORD_ARGON2I);

		if (
			!isset($_POST['login'], $_POST['password'], $_POST['password-repeat'])
			|| $_POST['password'] !== $_POST['password-repeat']
			|| \preg_match('/[A-Za-z0-9]{6,}/', $_POST['password']) !== 1
		) {
			$_SESSION['message']['error'] = 'Podane dane nie są poprawne';
			return Redirect::redirect('user/add');
		}

		$model = new UserModel();

		$id = $model->insert($login, $password);

		if ($id > 0) {
			$_SESSION['message']['success'] = 'Pomyślnie dodano użytkownika '.$login;
			return Redirect::redirect('user');
		} else {
			$_SESSION['message']['error'] = 'Nie udało się dodać użytkownika '.$login;
			return Redirect::redirect('user/add');
		}
	}

	public function permissionsForm()
	{
		if (!isset($_GET['id'])) {
			$_SESSION['message']['error'] = 'Nie podano użytkownika';
			return Redirect::redirect('user');
		}

		$model = new UserModel();
		$permModel = new PermissionModel();

		$view = new View();
		$view->template('userPermissions');

		$user = $model->find($_GET['id']);
		$assigned = $permModel->getUserPerms($_GET['id']);
		$permissions = $permModel->getAll();

		return $view->with([
			'user' => $user,
			'assiged' => array_keys($assigned),
			'permissions' => $permissions,
			'hello' => 'Witaj',
		]);
	}

	public function permissionsStore()
	{
		$userId = $_POST['user_id'];
		$permissions = array_keys($_POST['permission'] ?? []);

		$model = new UserModel();

		$effect = $model->updateUserPermissions($userId, $permissions);

		if ($effect >= 0) {
			$_SESSION['message']['success'] = 'Pomyślnie zaktualizowano użytkownika '.$login;
			return Redirect::redirect('user');
		} else {
			$_SESSION['message']['error'] = 'Nie udało się zaktualizować użytkownika '.$login;
			return Redirect::redirect('user/permissions-form/'.$userId.'/');
		}
	}

	public function loginForm()
	{
		if (isset($_SESSION['logged-in-user'])) {
			$_SESSION['message']['error'] = 'Jesteś już zalogowany.';
			return Redirect::redirect('/');
		}

		return (new View())->template('login');
	}


	public function login()
	{
		if (isset($_SESSION['logged-in-user'])) {
			$_SESSION['message']['error'] = 'Jesteś już zalogowany.';
			return Redirect::redirect('/');
		}

		$login = $_POST['login'];
		$password = $_POST['password'];

		$model = new UserModel();
		$user = $model->findByLogin($login);

		if (count($user) === 0) {
			$_SESSION['message']['error'] = 'Podany użytkownik nie został odnaleziony';
			return Redirect::redirect('user/login-form');
		}

		$passwordFromDb = $user['password'];

		$passwordIsCorrect = \password_verify($password, $passwordFromDb);

		if (false === $passwordIsCorrect) {
			$_SESSION['message']['error'] = 'Podane hasło jest niepoprawne';
			return Redirect::redirect('user/login-form');
		}

		$_SESSION['logged-in-user'] = $user;
		return Redirect::redirect();
	}

	public function logout()
	{
		unset($_SESSION['logged-in-user']);
		return Redirect::redirect('');
	}
}
