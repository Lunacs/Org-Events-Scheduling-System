{{--
    x-ui.loading — replaces `x-mary-loading`.

    A DaisyUI loading spinner. Forwards pass-through classes (size/color such as
    `loading-lg text-primary`) verbatim. Defaults to the spinner variant so it animates
    even when a call site only supplies a size/color class.
--}}
<span {{ $attributes->class(['loading loading-spinner']) }}></span>
