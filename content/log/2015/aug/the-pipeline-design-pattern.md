---
title: Design Patterns: The Pipeline
author: William
template: log
---

We use the Pipeline pattern in situations where data must flow through a pre-defined sequence of steps.

The basic purpose of the Pipeline is to offer a means for sequentially performing an arbitrary number of operations
on a subject. A convenient example of a Pipeline in the wild is the rendering of this HTML page.

Consider the following condensed route definition:

    $router->addRoute('GET', '/{slug:.*}', function ($c, $req) {
        $document = new DocumentPayload($c, $req->getPathInfo());

        $pipeline = (new Pipeline)
            ->pipe(new ResolveDocument)
            ->pipe(new ParseFrontMatter)
            ->pipe(new ParseContent)
            ->pipe(new RenderTemplate);

        $document = $pipeline->process($document);

        return new Response($document->getOutput(), 200);
    }

We first create a new instance of `DocumentPayload` which sets some sensible defaults (as you'll see later). This
instance will proceed to be passed through each stage defined in our `Pipeline` before finally terminating when there
are no more stages left in the operation.

The process of converting a HTTP request in to a HTML response has been distilled into a very clear sequence of
operations.

1. We resolve the document through `ResolveDocument`
2. We extract the YAML front matter through `ParseFrontMatter`
3. We transform the Markdown to HTML through `ParseContent`
4. We render the template through `RenderTemplate`

At the end of this sequence we have a fully hydrated DocumentPayload and can return the correct response.

The obvious benefit of using the Pipeline pattern is a clear API. A more subtle benefit is the added ability to make
your processes easily extensible by mixing and matching Pipelines.

## A quick note on the Chain of Responsibility

A rather similar pattern to the Pipeline is the Chain of Responsibility. The CoR defines both a Command object and a
Handler. The Command object is passed through each Handler in a chain. So far, so similar.

The big differentiator for the Pipeline pattern is a manager object. The manager is responsible for building the chain
in such a way that new handlers can be painlessly added at any stage of the pipeline.