CREATE TABLE IF NOT EXISTS [Students] ([id] INTEGER PRIMARY KEY UNIQUE, [name] VARCHAR(64), [reportsTo] INTEGER);
CREATE TABLE IF NOT EXISTS [Assignments] ([id] INTEGER PRIMARY KEY UNIQUE, [name] VARCHAR(64), [studentId] INTEGER);
CREATE VIEW Supervisors AS SELECT * FROM Students WHERE reportsTo IS NULL;
