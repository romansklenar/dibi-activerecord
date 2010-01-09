CREATE TABLE IF NOT EXISTS [albums] (
	[id] INTEGER PRIMARY KEY UNIQUE,
	[name] VARCHAR(64)
);

CREATE TABLE IF NOT EXISTS [songs] (
	[id] INTEGER PRIMARY KEY UNIQUE,
	[name] VARCHAR(64)
);

/* alias tracklists */
CREATE TABLE IF NOT EXISTS [albums_songs] (
	[album_id] INTEGER,
	[song_id] INTEGER
);