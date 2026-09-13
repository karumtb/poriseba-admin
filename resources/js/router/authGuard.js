export default function authGuard(to, from, next) {
    const token = localStorage.getItem('token')
    const requiresAuth = to.matched.some(
        route => route.meta.requiresAuth
    )

    if (requiresAuth && !token) {
        return next('/')
    }

    next()
}
