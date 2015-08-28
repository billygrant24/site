---
title: Avoid throwing generic exceptions
summary: Juggling exceptions is not fun.
template: log
---

I'll admit that I sometimes write code like this:

    if ( ! $filesystem->has($path)) {
        throw new RuntimeException(
            sprintf('File: %s does not exist.', $path)
        );
    }

It seems harmless enough on first inspection but complacency inevitably leads to unnecessary pain for consumers of
our software.

What is the problem, exactly?

What happens if more than one `RuntimeException` can be thrown in a given `try..catch` block?

## Communicate with clarity

Low-level code can not take for granted that the context it applies to a generic exception is the context which will
be read by the consumer.

Fortunately, if our library provides its own meaningful exceptions the consumer is immediately put in a better position
for reading the context.

Let's refactor our code slightly:

    if ( ! $filesystem->has($path)) {
        throw new Acme\FileNotFoundException(
            sprintf('File: %s does not exist.', $path)
        );
    }

Notice that we are now being specific. The consumer can handle `Acme\FileNotFoundException` with assurance.