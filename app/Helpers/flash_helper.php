<?php

/**
 * flash() — the session notifier the admin controllers already call.
 *
 * CustomerGroupController and MembershipPlanController were written against a `flash()->success()`
 * API that was never defined, so every one of their write routes ended in
 * "Call to undefined function App\Http\Controllers\Admin\flash()" *after* the row was saved: the
 * operator saw a crash, resubmitted, and got a duplicate. The admin layout renders session
 * success/error/warning/info through adminToast(), so that is what this writes.
 *
 * Kept in the global namespace on purpose — the callers use the bare `flash()`.
 */
if (! function_exists('flash')) {
    class FlashNotifier
    {
        public function success(string $message): self
        {
            return $this->message('success', $message);
        }

        public function error(string $message): self
        {
            return $this->message('error', $message);
        }

        public function warning(string $message): self
        {
            return $this->message('warning', $message);
        }

        public function info(string $message): self
        {
            return $this->message('info', $message);
        }

        /** Queue one message for the next request. */
        protected function message(string $level, string $message): self
        {
            session()->flash($level, $message);

            // Some controllers use flash()->success(...) then `return back()`; the flash has to
            // survive that redirect, so this must be a real session flash and not a view share.
            return $this;
        }

        /** Array of everything queued, for callers that want to inspect it. */
        public function all(): array
        {
            $out = [];
            foreach (['success', 'error', 'warning', 'info'] as $level) {
                if (session()->has($level)) {
                    $out[$level] = session($level);
                }
            }

            return $out;
        }
    }

    function flash(): FlashNotifier
    {
        return new FlashNotifier();
    }
}
