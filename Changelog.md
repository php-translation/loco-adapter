# Change Log

The change log describes what is "Added", "Removed", "Changed" or "Fixed" between each release.

## 0.9.0

- Remove support of PHP < 7.2
- Remove support of symfony components < 3.4
- Add support for symfony ^5.0

## 0.8.0

### Added

- Support for stable versions of php-translation/common and php-translation/storage

## 0.7.0

### Added

- Better support for managing multiple domains in one Loco project
- Support for latest php-translation/common and storage

## 0.6.2

### Fixed

- Export will filter on domain.

## 0.6.1

### Fixed

- Syntax error

## 0.6.0

### Added

- Make sure we can configure what index key we should use with Loco. This will fix duplicate message issue.  

## 0.5.0

### Added

- Support for Symfony 4

## 0.4.0

### Changed

- Skip creation of translations that are the same as their key 
- Bumped version of php-translation/symfony-storage

## 0.3.1

### Fixed

- Fixed bug when Translation not found. `Loco::get` should not throw exception.

## 0.3.0

### Changed

- Only export translated strings

## 0.2.1

### Added

- Add the translation parameters as "Notes" in Loco.

## 0.2.0

### Added

- Added support for `TransferableStorage`

### Changed

- `Loco::getApiKey()` is now private

## 0.1.0

Init release
