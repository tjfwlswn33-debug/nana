# 여백의 기록 — 아보카도 게시판 스킨 v3

PHP 7.4용 캐릭터 프로필·세계관 타임라인 스킨입니다.

**[스킨 ZIP 다운로드](https://github.com/tjfwlswn33-debug/nana/raw/refs/heads/main/dist/nana-board-skins-v3.zip)** · [설치·업데이트 안내](skin/INSTALL.md)

## v3 변경

- 목록 복귀 주소를 게시판 ID로 직접 생성하고 검색·분류·페이지를 유지
- 흰색 본문 박스와 대비가 있는 짙은 파란색 글자, 기존 개인홈 글꼴 상속
- 연표 제목 오른쪽의 전체 타임라인·추가 버튼, 글쓴이·해당 관리자용 항목별 수정 버튼
- 연표 이미지를 누르면 해당 사건 글로 이동
- 소개글 → 연표 이미지 → 캐릭터 목록을 한 화면에 구성
- 게시판 내 Directory 메뉴, 모바일에서는 가로 메뉴
- 캐릭터 상세: 이름·초상·소개·기본 정보와 소제목별 긴 본문
- PHP 7.4 검사 및 Chromium의 모바일·데스크톱·iframe 탐색 검사

## 설치

ZIP의 `skin/board/nana_character`, `skin/board/nana_timeline`을 아보카도 서버의 같은 경로에 업로드하고 게시판 관리자에서 선택합니다. 기존 버전은 먼저 백업하세요. 루트 `index.php`, `main.php`, `head.php`, `tail.php`는 교체하지 않습니다.

`settings.local.php`에 소개글과 실제 캐릭터·연표 게시판 ID를 지정하면 연표 미리보기와 상호 이동 메뉴를 사용할 수 있습니다. 설정 예시는 설치 안내에 있습니다. 기존 테마 메뉴가 보이면 보조 메뉴를 끌 수 있습니다.

## 화면 예시

테스트용 콘텐츠·임시 헤더·BGM 조작부를 사용한 이미지입니다. 실제 사이트 화면이나 실제 음악 재생을 검증한 결과는 아닙니다.

![목록 예시](docs/previews/overview.png)

![프로필 예시](docs/previews/profile.png)

## 검증

```sh
docker run --rm -v "$PWD:/app:ro" -w /app php:7.4-cli php tests/skin-test.php
docker run --rm -v "$PWD:/app:ro" -w /app php:7.4-cli php tests/public-data-test.php
```

브라우저 검사는 `tests/fixtures/render.php`로 만든 정적 엔진 대역에 `tests/fixtures/serve.py`를 실행하고 `tests/browser-test.js`로 확인합니다. Playwright와 Chromium이 필요하며 테스트 결과 PNG는 `/tmp/nana-v2-preview`에 저장합니다.

실제 아보카도 DB·캡차·업로드 통합 검증은 해당 서버에서 별도로 필요합니다. 기존 커스텀 메뉴·BGM의 실제 표시와 음악 재생은 해당 사이트 테마와 함께 확인해야 합니다. 스킨은 루트 인트로·헤더·플레이어 파일을 교체하거나 숨기지 않습니다.

글쓰기 화면은 Gnuboard5 기본 스킨을 바탕으로 수정했으며 GPL-2.0-or-later 조건을 따릅니다. 라이선스는 각 스킨 폴더에 포함했습니다.
