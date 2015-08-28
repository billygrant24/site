---
title: Use the Null Object pattern to simplify your code
summary: The Null Object pattern allows you to write more robust code free of defensive boilerplate.
template: log
---

How often do you write code like this:

    if ( ! is_null($user) {
        $email = $user->getEmail();
    }

The null check ensures we don't try to call the `getEmail()` method on an empty `$user`.

Maybe we want `$email` to have a default value in this event, so our code might instead look like:

    $email = is_null($user) ? null : $user->getEmail();

It certainly works and isn't awful by any measure. However, consider you require this check in multiple locations
across your library - how do we remain <abbr title="Don't Repeat Yourself">DRY</abbr>?

This is where the Null Object pattern can guide us toward a solution. First, let's implement a `NullUser`:

    class NullUser implements UserInterface
    {
        public function getEmail()
        {
            // no value
        }
    }

 Now let's attempt to create an instance of User:

    if ( ! $user = (new User)->findById(42)) {
        $user = new NullUser;
    }

With that in place, our client code is now simplified such that when we want to retrieve the email from `$user` we
just use `getEmail()`. We no longer need the defensive null checks, instead we are left with a stunningly elegant API.

    // When User exists
    $email = $user->getEmail(); // hello@william.scot

    // When User doesn't exist
    $email = $user->getEmail(); // null