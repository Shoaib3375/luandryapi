import http from 'k6/http';
import { check, sleep } from 'k6';
import { Counter, Rate } from 'k6/metrics';

const registrationSuccess = new Counter('registration_success');
const registrationFailed = new Counter('registration_failed');
const errorRate = new Rate('error_rate');

// Generated once per test run — guarantees unique emails across runs
const RUN_ID = Date.now();

export let options = {
    stages: [
        { duration: '30s', target: 50  },   // ramp to 50 users
        { duration: '1m',  target: 100 },   // stress at 100 users
        { duration: '30s', target: 200 },   // spike to 200 users
        { duration: '30s', target: 0   },   // ramp down
    ],
    thresholds: {
        http_req_duration: ['p(95)<3000'],
        http_req_failed:   ['rate<0.01'],
    },
};

export default function () {
    const url = 'http://127.0.0.1/api/register';

    // RUN_ID + VU + ITER = guaranteed unique every run
    const uniqueEmail = `user_${RUN_ID}_${__VU}_${__ITER}@test.com`;

    const payload = JSON.stringify({
        name:                  `Test User ${__VU}`,
        email:                 uniqueEmail,
        password:              'password123',
        password_confirmation: 'password123',
    });

    const params = {
        headers: {
            'Content-Type': 'application/json',
            'Accept':        'application/json',
        },
    };

    const res = http.post(url, payload, params);

    let body = {};
    try {
        body = JSON.parse(res.body);
    } catch (e) {
        console.log(`Failed to parse body: ${res.body}`);
    }

    const success = check(res, {
        'status is 201':          (r) => r.status === 201,
        'success is true':        () => body.success === true,
        'has token':              () => body.data?.token !== undefined,
        'has user':               () => body.data?.user !== undefined,
        'response time < 2000ms': (r) => r.timings.duration < 2000,
    });

    if (success) {
        registrationSuccess.add(1);
    } else {
        registrationFailed.add(1);
        console.log(`FAILED [VU:${__VU} ITER:${__ITER}] Status: ${res.status} Body: ${res.body}`);
    }

    errorRate.add(!success);

    sleep(1);
}
