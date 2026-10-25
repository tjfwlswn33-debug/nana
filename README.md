# 여백의 기록 — 아보카도 게시판 스킨

PHP 7.4용 캐릭터 프로필·세계관 타임라인 스킨입니다.

## 다운로드

[스킨 ZIP 다운로드](https://github.com/tjfwlswn33-debug/nana/raw/refs/heads/main/dist/nana-board-skins.zip)

## 설치

1. ZIP의 `skin/board/nana_character`, `skin/board/nana_timeline`을 아보카도 서버의 `skin/board/`에 업로드합니다.
2. 게시판 관리자에서 캐릭터 게시판은 `nana_character`, 타임라인 게시판은 `nana_timeline` 스킨을 선택합니다.
3. 모바일 스킨이 별도인 버전에서는 `mobile/skin/board/`에도 복사하고 선택합니다.

기존 `index.php`, `main.php`, `head.php`, `tail.php`는 교체하지 않습니다.

[상세 설치 안내](skin/INSTALL.md)

## 기능

- 캐릭터 이미지 목록 → 개별 프로필
- 세로형 세계관 타임라인
- 직업·소속, 연도, 한 줄 소개 입력란
- 기본 엔진의 글쓰기·수정·삭제·첨부·검색·페이지 이동 사용
- 반응형 레이아웃 및 스킨 내부로 제한한 CSS

## 검증

```sh
docker run --rm -v "$PWD:/app:ro" -w /app php:7.4-cli php tests/skin-test.php
```

PHP 7.4.33에서 스킨 문법과 최소 엔진 변수에 의한 렌더링·비밀글 정보 숨김·폼 연결 검사를 통과했습니다. 실제 아보카도 서버에서 DB 저장·업로드·캡차의 통합 확인은 별도로 필요합니다.

글쓰기 화면은 Gnuboard5 기본 스킨을 바탕으로 수정했으며 GPL-2.0-or-later 조건을 따릅니다. 라이선스는 각 스킨 폴더에 포함했습니다.
