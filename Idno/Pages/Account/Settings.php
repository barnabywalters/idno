<?php

    /**
     * Change user settings
     */

    namespace Idno\Pages\Account {

        /**
         * Default class to serve the homepage
         */
        class Settings extends \Idno\Common\Page
        {

            function getContent()
            {
                $this->gatekeeper(); // Logged-in only please
                $t        = \Idno\Core\site()->template();
                $t->body  = $t->draw('account/settings');
                $t->title = 'Account settings';
                $t->drawPage();
            }

            function postContent()
            {
                $this->gatekeeper(); // Logged-in only please
                $user = \Idno\Core\site()->session()->currentUser();
                $name = $this->getInput('name');
                //$handle = $this->getInput('handle');
                $email     = $this->getInput('email');
                $password  = $this->getInput('password');
                $password2 = $this->getInput('password2');

                if (!empty($name)) {
                    $user->setTitle($name);
                }

                if (!empty($email) && $email != $user->email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    if (!\Idno\Entities\User::getByEmail($email)) {
                        $user->email = $email;
                    } else {
                        \Idno\Core\site()->session()->addMessage('Someone is already using ' . $email . ' as their email address.', 'alert-error');
                    }
                }

                if (!empty($password) && $password == $password2) {
                    $user->setPassword($password);
                }

                if (!empty($_FILES['avatar'])) {
                    if (in_array($_FILES['avatar']['type'], array('image/png', 'image/jpg', 'image/jpeg', 'image/gif'))) {
                        if (getimagesize($_FILES['avatar']['tmp_name'])) {
                            if ($icon = \Idno\Entities\File::createThumbnailFromFile($_FILES['avatar']['tmp_name'], $_FILES['avatar']['name'], 300)) {
                                $user->icon = (string)$icon;
                            } else if ($icon = \Idno\Entities\File::createFromFile($_FILES['avatar']['tmp_name'], $_FILES['avatar']['name'])) {
                                $user->icon = (string)$icon;
                            }
                        }
                    }
                }

                if ($user->save()) {
                    \Idno\Core\site()->session()->addMessage("Your details were saved.");
                }
                $this->forward($_SERVER['HTTP_REFERER']);
            }

        }

    }